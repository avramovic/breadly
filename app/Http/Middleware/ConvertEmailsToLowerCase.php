<?php

namespace App\Http\Middleware;

use Closure;

class ConvertEmailsToLowerCase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isset($request->email) && is_string($request->email)) {
            $data          = $request->all();
            $data['email'] = strtolower($data['email']);
            $request->replace($data);
        }

        return $next($request);
    }
}
