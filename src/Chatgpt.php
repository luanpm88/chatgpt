<?php

namespace Acelle\Chatgpt;

use Acelle\Model\Setting;
use Acelle\Model\Plugin;

class Chatgpt
{
    public const NAME = 'acelle/chatgpt';

    public $plugin;
    public $openAIApiKey;

    public function __construct()
    {
        $this->plugin = Plugin::where('name', self::NAME)->first();
        $this->openAIApiKey = $this->getOpenAIApiKeyFromSetting();
    }

    public static function initialize()
    {
        return (new self());
    }

    /**
     * Request PayPal service.
     *
     * @return void
     */
    private function request($uri, $type = 'GET', $options = [])
    {
        $client = new \GuzzleHttp\Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->gateway->secretKey,
        ];

        if (isset($options['headers'])) {
            $headers = array_merge($headers, $options['headers']);
        }

        $response = $client->request($type, $uri, [
            'headers' => $headers,
            'form_params' => isset($options['form_params']) ? $options['form_params'] : [],
            'body' => isset($options['body']) ? (is_array($options['body']) ? json_encode($options['body']) : $options['body']) : '',
        ]);

        return json_decode($response->getBody(), true);
    }

    public function saveAPISettings($params)
    {
        $chatgpt = $this;

        // make validator
        $validator = \Validator::make($params, [
            'openai_api_key' => 'required',
        ]);

        $chatgpt->publicKey = isset($params['openai_api_key']) ? $params['openai_api_key'] : null;

        // test service
        $validator->after(function ($validator) use ($params, $chatgpt) {
            try {
                // $chatgpt->test();
            } catch(\Exception $e) {
                $validator->errors()->add('field', 'Can not connect to ChatGPT. Error: ' . $e->getMessage());
            }
        });

        // redirect if fails
        if ($validator->fails()) {
            return $validator;
        }

        // save settings
        $this->saveOpenAIApiKeyToSetting($params['openai_api_key']);

        return $validator;
    }

    public function test()
    {
        throw new \Exception('test() function not emplement yet!');
    }

    public function getOpenAIApiKeyFromSetting()
    {
        return Setting::get('cashier.chatgpt.openai_api_key');
    }

    public function saveOpenAIApiKeyToSetting($openai_api_key)
    {
        $this->openAIApiKey = $openai_api_key;
        Setting::set('cashier.chatgpt.openai_api_key', $this->openAIApiKey);
    }

    public function getOpenAIApiKey()
    {
        return $this->openAIApiKey;
    }
}
