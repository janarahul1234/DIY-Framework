<?php

namespace App\Controllers;

use App\core\Controller;

class AuthController extends Controller
{
    public $layout = 'authLayout';

    public function login(): string
    {
        return view('auth/login');
    }

    public function register(): string
    {
        return view('auth/register');
    }

    public function user($userId): string
    {
        return "User Id: {$userId}";
    }
}