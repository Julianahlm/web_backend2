<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;


class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->header('Authorization');
        $user = User::where('remember_token', $token)->first();

        $authenticated = false;
        if ($token && $user) {
            $authenticated = true;
        }

        if ($authenticated) {
            Auth::login($user);
            return $next($request);
        } else {
            return response()->json([
                "error" => [
                    'message' => ['Unauthorized']
                ]
            ])->setStatusCode(401);
        }
    }
}
