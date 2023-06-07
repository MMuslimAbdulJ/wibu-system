<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function proseslogin(Request $request)
    {
        if (Auth::guard('mahasiswa')->attempt(['nim' => $request->nim, 'password' => $request->password])) {
            return redirect('/dashboard');
        } else {
            return redirect('/')->with(["warning" => "NIM atau Password anda salah"]);
        }
    }

    public function proseslogout() {
        if(Auth::guard('mahasiswa')->check()) {
            Auth::guard('mahasiswa')->logout();
            return redirect('/');
        } else {
            return "Gagal Logout";
        }
    }
}
