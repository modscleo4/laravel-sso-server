<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Server;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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
    }

    /**
     * Show the application's login form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {
        $return_url = $request->return_url;
        $session_id = $request->session_id;
        $broker = Session::get('broker');

        if ($broker) {
            Session::remove('broker');

            if (!$return_url) {
                $return_url = $broker->return_url;

                if (!$return_url) {
                    abort(404);
                }
            }

            if (!$session_id) {
                $session_id = $broker->session_id;
            }
        }

        return view('auth.login')->with([
            'session_id' => $session_id,
            'return_url' => $return_url,
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if ($request->filled('return_url') && $request->filled('session_id')) {
            $this->redirectTo = $request->return_url;
            $server = new Server();
            $server->manualLogin($request->session_id, $user->email);
        } else {
            Session::put('sso_user', $user->email);
        }

        return redirect($this->redirectPath());
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    protected function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/');
    }
}
