<?php

namespace App\Modules\Support\Controllers;

use App\Http\Controllers\Controller;

class SupportController extends Controller
{
    public function index()
    {
        return view('modules.support.index');
    }
}