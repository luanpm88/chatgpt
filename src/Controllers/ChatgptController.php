<?php

namespace Acelle\Chatgpt\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller as BaseController;
use Acelle\Chatgpt\Chatgpt;

class ChatgptController extends BaseController
{
    public function settings(Request $request)
    {
        $chatgpt = Chatgpt::initialize();

        if ($request->isMethod('post')) {
            // save Chatgpt setting
            $validator = $chatgpt->saveAPISettings($request->all());

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('chatgpt::settings', [
                    'chatgpt' => $chatgpt,
                    'errors' => $validator->errors(),
                ], 400);
            }

            if ($request->enable) {
                $chatgpt->plugin->activate();
            }

            return redirect()->action("Admin\PluginController@index")
                ->with('alert-success', trans('chatgpt::messages.settings.updated'));
        }

        return view('chatgpt::settings', [
            'chatgpt' => $chatgpt,
        ]);
    }
}
