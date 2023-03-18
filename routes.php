<?php

// Client View Groups
Route::group(['middleware' => ['web'], 'namespace' => '\Acelle\Chatgpt\Controllers'], function () {
    Route::get('plugins/acelle/chatgpt', 'DashboardController@index');

    // 
    Route::match(['get', 'post'], 'plugins/acelle/chatgpt/settings', 'ChatgptController@settings');
});
