<?php

namespace Acelle\Chatgpt;

use Illuminate\Support\ServiceProvider as Base;
use Acelle\Library\Facades\Hook;
use Acelle\Chatgpt\Chatgpt;

class ServiceProvider extends Base
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Register views path
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'chatgpt');

        // Register routes file
        $this->loadRoutesFrom(__DIR__.'/../routes.php');

        // Register translation file
        $this->loadTranslationsFrom(storage_path('app/data/plugins/acelle/chatgpt/lang/'), 'chatgpt');

        // Register the translation file against Acelle translation management
        Hook::register('add_translation_file', function () {
            return [
                "id" => '#acelle/chatgpt_translation_file',
                "plugin_name" => "acelle/chatgpt",
                "file_title" => "Translation for acelle/chatgpt plugin",
                "translation_folder" => storage_path('app/data/plugins/acelle/chatgpt/lang/'),
                "file_name" => "messages.php",
                "master_translation_file" => realpath(__DIR__.'/../resources/lang/en/messages.php'),
            ];
        });

        // Activate hook
        Hook::register('activate_plugin_acelle/chatgpt', function () {
            $chatgpt = Chatgpt::initialize();
            $chatgpt->test();
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
