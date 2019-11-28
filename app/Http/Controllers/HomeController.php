<?php

namespace App\Http\Controllers;

use App\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->password_change_at == null) {
            return redirect()->route('user.password.form');
        }

        $data = [];

        $data['brokers'] = $user->brokers();
        return view('home')->with($data);
    }
}
