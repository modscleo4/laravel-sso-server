<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Server;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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
        $genCookie = true;
        $return_url = null;
        $broker = Session::get('broker');

        if (!$broker && Cookie::has('__broker')) {
            $broker = (object)json_decode(Cookie::get('__broker'), true);
            $genCookie = false;
        }

        if ($broker) {
            $return_url = $request->return_url;
            if (!$return_url) {
                $return_url = $broker->return_url;
            }

            if ($return_url == null) {
                abort(404);
            }

            if ($genCookie) {
                Cookie::queue(Cookie::make('__broker', json_encode($broker->toArray()), 60));
            }
        }

        return view('auth.login')->with(['return_url' => $return_url]);
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
        if (Cookie::has('__broker')) {
            $this->redirectTo = $request->return_url;

            $broker = (object)json_decode(Cookie::get('__broker'), true);
            $server = new Server();
            $server->manualLogin($broker->session_id, $user->email);
            Cookie::queue(Cookie::forget('__broker'));
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
