<?php

namespace App\Controllers;

use App\core\Controller;

class PageController extends Controller
{
    public $layout = 'masterLayout';

    public function index(): string
    {
        return view('pages/home');
    }

    public function about(): string
    {
        return view('pages/about', [
            'name' => 'Rahul Jana',
            'age' => 20,
        ]);
    }

    public function contact(): string
    {
        return view('pages/contact');
    }
}
