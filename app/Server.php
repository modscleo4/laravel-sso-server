<?php

namespace App;

use App\Models\Broker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Jasny\SSO\Exception;

class Server extends \Jasny\SSO\Server
{
    /**
     * Attach user's session to broker's session.
     *
     * @param string|null $broker Broker's name/id.
     * @param string|null $token Token sent from broker.
     * @param string|null $checksum Calculated broker+token checksum.
     *
     * @return void
     */
    public function attach($broker = null, $token = null, $checksum = null)
    {
        try {
            if (!$broker) {
                $this->fail('No broker id specified.', true);
            }

            if (!$token) {
                $this->fail('No token specified.', true);
            }

            if (!$checksum || $checksum != $this->generateAttachChecksum($broker, $token)) {
                $this->fail('Invalid checksum.', true);
            }

            $this->startUserSession();
            $sessionId = $this->generateSessionId($broker, $token);
            $this->saveBrokerSessionData($sessionId, $this->getSessionData('id'));

            $this->attachSuccess();
        } catch (Exception $e) {
            $this->redirect(null, ['sso_error' => $e->getMessage()]);
        }
    }

    /**
     *
     * @param Request $request
     *
     * @return string
     */
    public function loginForm(Request $request)
    {
        try {
            if (!$request->broker) {
                $this->fail('No broker id specified.', true);
            }

            $broker_id = $request->broker;
            $broker = $this->getBrokerInfo($broker_id);
            $broker->setAttribute('return_url', $request->return_url);
            $broker->setAttribute('session_id', $request->session_id);
        } catch (Exception $e) {
            $this->redirect(null, ['sso_error' => $e->getMessage()]);
        }

        return redirect()->route('login')->with(['broker' => $broker]);
    }

    public function passwordForm(Request $request)
    {
        try {
            if (!$request->broker) {
                $this->fail('No broker id specified.', true);
            }

            $broker_id = $request->broker;
            $broker = $this->getBrokerInfo($broker_id);

            $return_url = $request->return_url;
        } catch (Exception $e) {
            $this->redirect(null, ['sso_error' => $e->getMessage()]);
        }

        return redirect()->route('user.password.form')->with(['return_url' => $return_url]);
    }

    /**
     * @param $sessionId
     * @param $email
     */
    public function manualLogin($sessionId, $email)
    {
        $savedSessionId = $this->getBrokerSessionData($sessionId);
        $this->startSession($savedSessionId);
        $this->setSessionData('sso_user', $email);
    }

    /**
     * @param null|string $email
     * @param null|string $password
     * @param null|bool $remember
     *
     * @return string
     */
    public function login($email = null, $password = null, $remember = false)
    {
        try {
            $this->startBrokerSession();
            if (!$email || !$password) {
                $this->fail('No email and/or password provided.');
            }

            if (!$this->authenticate($email, $password, $remember)) {
                $this->fail('User authentication failed.');
            }

        } catch (Exception $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        $this->setSessionData('sso_user', $email);
        return $this->userInfo();
    }

    /**
     * Logging user out.
     *
     * @return string
     */
    public function logout()
    {
        try {
            $this->startBrokerSession();
            $this->setSessionData('sso_user', null);

            Auth::logout();
        } catch (Exception $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        return $this->returnJson(['success' => 'User has been successfully logged out.']);
    }

    /**
     * Returning user info for the broker.
     *
     * @return string
     */
    public function userInfo()
    {
        try {
            $this->startBrokerSession();

            if (!Auth::check()) {
                $username = $this->getSessionData('sso_user');
            } else {
                $username = Auth::user()->email;
            }

            if (!$username) {
                $this->fail("User not authenticated. Session ID: {$this->getSessionData('id')}");
            }

            if (!$user = $this->getUserInfo($username)) {
                $this->fail('User not found.');
            }

            if (!$user->can("{$this->brokerId}")) {
                $this->fail('User cannot access this Broker.');
            }
        } catch (Exception $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        return $this->returnUserInfo($user);
    }

    /**
     * Resume broker session if saved session id exist.
     *
     * @return void
     * @throws Exception
     *
     */
    public function startBrokerSession()
    {
        if (isset($this->brokerId)) {
            return;
        }

        $sessionId = $this->getBrokerSessionId();
        if (!$sessionId) {
            $this->fail('Missing session key from broker.');
        }

        $savedSessionId = $this->getBrokerSessionData($sessionId);
        if (!$savedSessionId) {
            $this->fail('There is no saved session data associated with the broker session id.');
        }

        $this->startSession($savedSessionId);
        $this->brokerId = $this->validateBrokerSessionId($sessionId);
    }

    /**
     * Check if broker session is valid.
     *
     * @param string $sessionId Session id from the broker.
     *
     * @return string
     * @throws Exception
     *
     */
    protected function validateBrokerSessionId($sessionId)
    {
        $matches = null;
        if (!preg_match('/^SSO-(\w*+)-(\w*+)-([a-z0-9]*+)$/', $this->getBrokerSessionId(), $matches)) {
            $this->fail('Invalid session id');
        }

        if ($this->generateSessionId($matches[1], $matches[2]) != $sessionId) {
            $this->fail('Checksum failed: Client IP address may have changed');
        }

        return $matches[1];
    }

    /**
     * Generate session id from session token.
     *
     * @param string $brokerId
     * @param string $token
     *
     * @return string
     * @throws Exception
     *
     */
    protected function generateSessionId($brokerId, $token)
    {
        $broker = $this->getBrokerInfo($brokerId);
        if (!$broker) {
            $this->fail('Provided broker does not exist.');
        }

        return "SSO-{$brokerId}-{$token}-" . hash('sha256', "session{$token}{$broker['secret']}");
    }

    /**
     * Generate session id from session token.
     *
     * @param string $brokerId
     * @param string $token
     *
     * @return string
     * @throws Exception
     *
     */
    protected function generateAttachChecksum($brokerId, $token)
    {
        $broker = $this->getBrokerInfo($brokerId);
        if (!$broker) {
            $this->fail('Provided broker does not exist.');
        }

        return hash('sha256', "attach{$token}{$broker['secret']}");
    }

    /**
     * Do things if attaching was successful.
     *
     * @return void
     */
    protected function attachSuccess()
    {
        $this->redirect();
    }

    /**
     * If something failed, throw an Exception or redirect.
     *
     * @param null|string $message
     * @param bool $isRedirect
     * @param null|string $url
     *
     * @return void
     * @throws Exception
     *
     */
    protected function fail($message, $isRedirect = false, $url = null)
    {
        if (!$isRedirect) {
            throw new Exception($message);
        }

        $this->redirect($url, ['sso_error' => $message]);
    }

    /**
     * Redirect to provided URL with query string.
     *
     * If $url is null, redirect to url which given in 'return_url'.
     *
     * @param string|null $url URL to be redirected.
     * @param array $parameters HTTP query string.
     * @param int $httpResponseCode HTTP response code for redirection.
     *
     * @return void
     */
    protected function redirect($url = null, $parameters = [], $httpResponseCode = 307)
    {
        if (!$url) {
            $url = urldecode(request()->get('return_url', null));
        }

        $query = '';
        // Making URL query string if parameters given.
        if (!empty($parameters)) {
            $query = '?';

            if (parse_url($url, PHP_URL_QUERY)) {
                $query = '&';
            }

            $query .= http_build_query($parameters);
        }

        app()->abort($httpResponseCode, '', ['Location' => $url . $query]);
    }

    /**
     * Returning json response for the broker.
     *
     * @param null|array $response Response array which will be encoded to json.
     * @param int $httpResponseCode HTTP response code.
     *
     * @return string
     */
    protected function returnJson($response = null, $httpResponseCode = 200)
    {
        return response()->json($response, $httpResponseCode);
    }

    /**
     * Authenticate using user credentials
     *
     * @param string $email
     * @param string $password
     * @param bool $remember
     *
     * @return bool|array
     */
    protected function authenticate($email, $password, $remember = false)
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            return false;
        }

        // After authentication Laravel will change session id, but we need to keep
        // this the same because this session id can be already attached to other brokers.
        $sessionId = $this->getBrokerSessionId();
        $savedSessionId = $this->getBrokerSessionData($sessionId);
        $this->startSession($savedSessionId);

        return true;
    }

    /**
     * Get the secret key and other info of a broker
     *
     * @param string $brokerId
     *
     * @return null|Broker
     */
    protected function getBrokerInfo($brokerId)
    {
        return Broker::where('name', '=', $brokerId)->firstOrFail();
    }

    /**
     * Get the information about a user
     *
     * @param string $email
     *
     * @return array|object|null
     */
    protected function getUserInfo($email)
    {
        return User::where('email', '=', $email)->firstOrFail();
    }

    /**
     * Returning user info for broker. Should return json or something like that.
     *
     * @param array|object $user Can be user object or array.
     *
     * @return mixed
     */
    protected function returnUserInfo($user)
    {
        return json_encode(['data' => $user], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Return session id sent from broker.
     *
     * @return null|string
     */
    protected function getBrokerSessionId()
    {
        $authorization = request()->header('Authorization', null);
        if ($authorization && strpos($authorization, 'Bearer') === 0) {
            return substr($authorization, 7);
        }

        return null;
    }

    /**
     * Start new session when user visits server.
     *
     * @return void
     */
    protected function startUserSession()
    {
        // Session must be started by middleware.
    }

    /**
     * Set session data
     *
     * @param string $key
     * @param null|string $value
     *
     * @return void
     */
    protected function setSessionData($key, $value = null)
    {
        if (!$value) {
            Session::forget($key);
            return;
        }

        Session::put($key, $value);
    }

    /**
     * Get data saved in session.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getSessionData($key)
    {
        if ($key === 'id') {
            return Session::getId();
        }

        return Session::get($key, null);
    }

    /**
     * Start new session with specific session id.
     *
     * @param $sessionId
     *
     * @return void
     */
    protected function startSession($sessionId)
    {
        Session::setId($sessionId);
        Session::start();
    }

    /**
     * Save broker session data to cache.
     *
     * @param string $brokerSessionId
     * @param string $sessionData
     *
     * @return void
     */
    protected function saveBrokerSessionData($brokerSessionId, $sessionData)
    {
        Cache::put("broker_session:{$brokerSessionId}", $sessionData, now()->addHour());
    }

    /**
     * Get broker session data from cache.
     *
     * @param string $brokerSessionId
     *
     * @return null|string
     */
    protected function getBrokerSessionData($brokerSessionId)
    {
        return Cache::get("broker_session:{$brokerSessionId}");
    }
}
