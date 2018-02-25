<?php namespace App\Http\Controllers\Api;

use App\Breadly\Services\BreadService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    /** @var User|null */
    protected $user = null;

    const ACTION_BROWSE = 'browse';
    const ACTION_READ = 'read';
    const ACTION_EDIT = 'edit';
    const ACTION_ADD = 'add';
    const ACTION_DELETE = 'delete';

    /**
     * Initialize authenticated user (if any)
     *
     */
    public function __construct()
    {
        $this->user = $this->getAuthUser();
    }

    /**
     *
     *
     * @param string $content
     * @param int    $status
     *
     * @return Response
     */
    protected function response($content, $status = 200)
    {
        return response($content, $status)
            ->header('Content-Type', 'text/plain')
            ->header('X-Request-Route', app('request')->route()->getName())
            ->header('X-Request-Uri', app('request')->path())
            ->header('X-Request-Tag', app('request')->header('X-Request-Tag'))
            ;
    }

    /**
     * Get authenticated user or fail.
     *
     * @return User
     * @throws JWTException
     */
    protected function getAuthUserOrFail()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * Get authenticated user without throwing an error if no user is authenticated.
     *
     * @return User|null
     */
    protected function getAuthUser()
    {
        try {
            $user = $this->getAuthUserOrFail();
        } catch (JWTException $e) {
            return null;
        }

        return $user;
    }

}