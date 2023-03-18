<?php

namespace Acelle\Chatgpt\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller as BaseController;
use Acelle\Model\Plugin;
use Acelle\Chatgpt\Chatgpt;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        return view('chatgpt::index', [
            'chatgpt' => Chatgpt::initialize(),
        ]);
    }
}
