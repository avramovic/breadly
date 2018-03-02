<?php namespace App\Http\Controllers\Api;

use App\Breadly\Components\JsonOutput;
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
     * @param mixed $content
     * @param int    $status
     * @param array  $extras
     *
     * @return Response
     */
    public function response($content, $status = 200, $extras = [])
    {
        return JsonOutput::httpResponse($content, $status, $extras);
    }

    public function error($error, $status = 500)
    {
        return JsonOutput::httpResponse($error, $status);
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
        if (empty($this->user)) {
            try {
                $this->user = $this->getAuthUserOrFail();
            } catch (JWTException $e) {
                return null;
            }
        }

        return $this->user;
    }

}