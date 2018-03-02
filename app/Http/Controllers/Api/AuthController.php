<?php namespace App\Http\Controllers\Api;

use App\Events\BreadProfileUpdated;
use App\Events\BreadUserRegistered;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\RegisterUserApiRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileApiRequest;
use App\Models\User;
use App\Notifications\SendPasswordResetCodeEmail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends ApiController
{
    public function authenticate(Request $request)
    {
        if ($this->user) {
            return $this->error("You are already logged in.", 403);
        }

        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->error("Invalid credentials", 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return $this->response($e->getMessage(), 500);
        }

        $user = JWTAuth::setToken($token)->authenticate();

        if (!$user->is_verified) {
            return $this->error("User not activated", 401);
        }

        // all good so return the token
        return $this->response($token, "Log in successful.");
    }

    public function profile()
    {
        $user = $this->getAuthUserOrFail();
        return $this->response($user->toArray());
    }

    public function updateProfile(UpdateProfileApiRequest $request)
    {
        $user = $this->getAuthUserOrFail();
        $data = $request->all();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        if (isset($data['deleted_at'])) {
            unset($data['deleted_at']);
        }

        if (isset($data['created_at'])) {
            unset($data['created_at']);
        }

        if (isset($data['role_id'])) {
            unset($data['role_id']);
        }

        $data['updated_at'] = Carbon::now();

        $response = DB::table('users')
            ->where('id', $user->id)
            ->update($data);

        event(new BreadProfileUpdated($user));

        return $this->response(
            $response,
            "Profile has been updated successfully."
        );
    }

    public function register(RegisterUserApiRequest $request)
    {
        if ($this->user) {
            return $this->error("You are already registered.", 403);
        }

        $data             = $request->all();
        $data['password'] = bcrypt($data['password']);
        $data['role_id']  = 2;

        if (setting('site.require_email_activation') == 1) {
            $data['verification_token'] = str_random(30);
            $data['is_verified']        = false;
        } else {
            $data['verification_token'] = null;
            $data['is_verified']        = true;
        }

        if (\Schema::hasColumn('users', 'guid')) {
            $data['guid'] = Uuid::uuid4()->toString();
        }

        /** @var User $newUser */
        $newUser = User::create($data);

        if ($newUser) {
            event(new BreadUserRegistered($newUser));
            return $this->response(
                $newUser->toArray(),
                "User has been registered successfully."
            );
        }

        return $this->error('Error creating user!', 500);

    }

    public function sendResetPasswordEmail(ForgotPasswordRequest $request)
    {
        if ($this->getAuthUser()) {
            return $this->error("You are already logged in.", 403);
        }

        $user = User::where('email', $request->email)->first();

        \DB::table('password_resets')
            ->where('created_at', '<', Carbon::now()->subMinutes((int)config('auth.passwords.users.expire', 60)))
            ->delete();

        $token = Password::broker()->createToken($user);
        $code  = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

        \DB::table('password_resets')
            ->where('email', $request->email)
            ->update([
                'code' => $code,
            ]);

        //@TODO: make event
        $user->notify(new SendPasswordResetCodeEmail($token, $code));

        return $this->message("A forgot password email has been sent!");
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        if ($this->getAuthUser()) {
            return $this->error("You are already logged in.", 403);
        }

        $entry = \DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$entry) {
            return $this->error("Reset password request with that email does not exist!", 422);
        }

        if ($entry->code !== $request->code) {
            return $this->error("Wrong password recovery code!", 422);
        }

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        \DB::table('password_resets')
            ->where('email', $user->email)
            ->where('code', $request->code)
            ->limit(1)
            ->delete();

        return $this->message("New password is successfully set.");
    }
}