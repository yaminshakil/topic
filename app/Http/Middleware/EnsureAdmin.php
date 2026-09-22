<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('tracker_admin')) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'error' => 'Only the admin can change topic status here.'], 403);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
