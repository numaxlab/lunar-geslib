<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateGeslibApi
{
    public function handle(Request $request, Closure $next)
    {
        $username = $request->query('usuario');
        $password = $request->query('clave');

        if (
            empty($username) || empty($password) ||
            (config('lunar.geslib.api.username') !== $username || config('lunar.geslib.api.password') !== $password)
        ) {
            return response()->xml(['error' => 'Invalid credentials'], 401);
        }

        return $next($request);
    }
}
