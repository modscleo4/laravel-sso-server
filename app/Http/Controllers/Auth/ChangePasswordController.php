<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\CurrentPassword;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ChangePasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use RedirectsUsers;

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
        $this->middleware('auth');
    }

    /**
     * Display the password change view for the given token.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showChangeForm(Request $request, $token = null)
    {
        $return_url = null;
        $return_url = Session::get('return_url');
        if (!$return_url) {
            $return_url = $request->return_url;
        }

        return view('auth.passwords.change')->with(['return_url' => $return_url]);
    }

    /**
     * Change the given user's password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function change(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to change the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $this->changePassword(Auth::user(), $this->credentials($request)['password']);

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $this->sendChangeResponse($request);
    }

    /**
     * Get the password change validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'current_password' => [(\App\Auth::user()->password_change_at != null) ? 'required' : '', new CurrentPassword],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }

    /**
     * Get the password change validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [];
    }

    /**
     * Get the password change credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'current_password', 'password', 'password_confirmation'
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param Authenticatable $user
     * @param string $password
     * @return void
     */
    protected function changePassword(Authenticatable $user, $password)
    {
        $user->password = Hash::make($password);
        $user->password_change_at = Carbon::now();

        $user->setRememberToken(Str::random(60));

        $user->save();
    }

    /**
     * Get the response for a successful password change.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendChangeResponse(Request $request)
    {
        if ($request->return_url) {
            $this->redirectTo = "{$request->return_url}?password_updated=true";
        }

        return redirect($this->redirectPath())
            ->with(['saved' => true, 'status' => trans('passwords.change')]);
    }

    /**
     * Get the broker to be used during password change.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * Get the guard to be used during password change.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
