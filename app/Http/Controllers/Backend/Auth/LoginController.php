<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::ADMIN_DASHBOARD;

    /**
     * show login form for admin guard
     *
     * @return void
     */
    public function showLoginForm()
    {
        return view('backend.auth.login');
    }


    /**
     * login admin
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        // Validate Login Data
        $request->validate([
            'email' => 'required|max:50',
            'password' => 'required',
        ]);

        // Check for too many login attempts
        $key = 'login-attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) { // Allows 5 attempts
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        // Get admin by email
        $admin = Admin::where('email', $request->email)
            ->orWhere('username', $request->email)
            ->first();
        // Check if admin exists and IP matches (skip for superadmin)
        if ($admin && !$admin->hasRole('superadmin') && $admin->ip_address != $request->ip()) {
            session()->flash('error', __('admins.unauthorized_ip_address'));
            return back();
        }

        // Attempt to login using email
        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            RateLimiter::clear($key); // Reset login attempts on successful login
            session()->flash('success', 'Successfully Logged in!');
            return redirect()->route('admin.product.index');
        }

        // Attempt to login using username
        if (Auth::guard('admin')->attempt(['username' => $request->email, 'password' => $request->password], $request->remember)) {
            RateLimiter::clear($key); // Reset login attempts on successful login
            session()->flash('success', 'Successfully Logged in!');
            return redirect()->route('admin.product.index');
        }

        // Increment login attempts on failure
        RateLimiter::hit($key, 60); // Lock for 1 minute after 5 attempts

        // Error
        session()->flash('error', __('messages.invalid_email_password'));
        return back();
    }

    /**
     * logout admin guard
     *
     * @return void
     */
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
