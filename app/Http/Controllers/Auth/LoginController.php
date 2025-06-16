<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
    
    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Log user info for debugging
        Log::info('User authenticated', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'redirect_param' => $request->input('redirect')
        ]);
        
        // If user is admin, redirect to admin dashboard
        if ($user->isAdmin()) {
            Log::info('Redirecting admin user to dashboard');
            if ($request->has('redirect') && strpos($request->input('redirect'), '/admin/') !== false) {
                return redirect($request->input('redirect'));
            }
            return redirect('/admin');
        }
        
        // Check if there's a redirect parameter
        if ($request->has('redirect')) {
            Log::info('Redirecting user to: ' . $request->input('redirect'));
            return redirect($request->input('redirect'));
        }
        
        Log::info('Redirecting user to default location: ' . $this->redirectPath());
        return redirect()->intended($this->redirectPath());
    }
    
    /**
     * Show the application's login form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showLoginForm(Request $request)
    {
        // Log the redirect parameter for debugging
        Log::info('Login form shown with redirect', [
            'redirect' => $request->input('redirect'),
            'url' => $request->fullUrl()
        ]);
        
        $redirect = $request->input('redirect', '');
        return view('auth.login', compact('redirect'));
    }
}
