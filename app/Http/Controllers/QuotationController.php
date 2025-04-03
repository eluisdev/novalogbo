<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    //

    public function index()
    {

        if(Auth::user()->role->whereIn('description', 'admin')->count() > 0) {

        }
        return view("quotations.dashboard");
    }
}
