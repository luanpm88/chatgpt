<?php

namespace Acelle\Chatgpt\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller as BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        return redirect()->action('\Acelle\Chatgpt\Controllers\ChatgptController@settings');
    }
}
