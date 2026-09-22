<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Signs out an employee who was deactivated while still logged in. */
class EnsureEmployeeActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $employee = Auth::guard('employee')->user();

        if ($employee && ! $employee->is_active) {
            Auth::guard('employee')->logout();
            $request->session()->regenerate();

            return redirect()->route('login')
                ->withErrors(['username' => 'Your account has been deactivated. Contact the admin.']);
        }

        return $next($request);
    }
}
