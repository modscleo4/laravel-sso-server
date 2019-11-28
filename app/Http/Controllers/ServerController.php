<?php

namespace App\Http\Controllers;

use App\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    /**
     * @param Request $request
     * @param Server $server
     *
     * @return void
     */
    public function attach(Request $request, Server $server)
    {
        $server->attach(
            $request->get('broker', null),
            $request->get('token', null),
            $request->get('checksum', null)
        );
    }

    public function loginForm(Request $request, Server $server)
    {
        return $server->loginForm($request);
    }

    public function passwordForm(Request $request, Server $server)
    {
        return $server->passwordForm($request);
    }

    /**
     * @param Request $request
     * @param Server $server
     *
     * @return mixed
     */
    public function login(Request $request, Server $server)
    {
        return $server->login(
            $request->get('email', null),
            $request->get('password', null),
            $request->get('remember', null)
        );
    }

    /**
     * @param Server $server
     *
     * @return string
     */
    public function logout(Server $server)
    {
        return $server->logout();
    }

    /**
     * @param Server $server
     *
     * @return string
     */
    public function userInfo(Server $server)
    {
        return $server->userInfo();
    }
}
