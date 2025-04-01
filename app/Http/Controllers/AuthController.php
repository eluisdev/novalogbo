<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view("auth.login");
    }

    public function showForgotPasswordForm() 
    {
        return view("auth.forgot-password");
    }
    
    public function showNewPasswordForm() 
    {
        return view("auth.new-password");
    }
}
