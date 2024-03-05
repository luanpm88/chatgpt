<?php

namespace Acelle\Chatgpt;

use Acelle\Model\Setting;
use Acelle\Model\Plugin;
use Orhanerday\OpenAi\OpenAi;

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
            'Authorization' => 'Bearer ' . $this->getOpenAIApiKey(),
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

        $chatgpt->openAIApiKey = isset($params['openai_api_key']) ? $params['openai_api_key'] : null;

        // test service
        $validator->after(function ($validator) use ($params, $chatgpt) {
            try {
                $chatgpt->test();
            } catch(\Exception $e) {
                if (strpos($e->getMessage(), 'insufficient_quota') !== false) {
                    $validator->errors()->add('chatppt', 'The quota for ChatGPT has run out. Please upgrade your OpenAI plan.');
                } else {
                    $validator->errors()->add('field', 'The quota for ChatGPT has run out. Please upgrade your OpenAI plan.' . $e->getMessage());
                }
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

    public function getOpenAIApiKeyFromSetting()
    {
        return Setting::get('chatgpt.openai_api_key');
    }

    public function saveOpenAIApiKeyToSetting($openai_api_key)
    {
        $this->openAIApiKey = $openai_api_key;
        Setting::set('chatgpt.openai_api_key', $this->openAIApiKey);
    }

    public function getOpenAIApiKey()
    {
        return $this->openAIApiKey;
    }

    public function getBannerUrl()
    {
        $banner = $this->plugin->getStoragePath('banner.svg');
        return \Acelle\Helpers\generatePublicPath($banner);
    }

    public function test()
    {
        // no api key provied
        if (!$this->getOpenAIApiKey()) {
            throw new \Exception(trans('chatgpt::messages.no_api_key_found'));
        }

        return $this->chat([
            [
                "role" => "system",
                "content" => "You are a helpful assistant."
            ],
            [
                "role" => "user",
                "content" => "Who won the world series in 2020?"
            ],
            [
                "role" => "assistant",
                "content" => "The Los Angeles Dodgers won the World Series in 2020."
            ],
            [
                "role" => "user",
                "content" => "I love you so much"
            ],
        ]);
    }

    public function chat($messages)
    {
        $open_ai_key = $this->getOpenAIApiKey();
        $open_ai = new OpenAi($open_ai_key);

        $complete = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => 1.0,
            'max_tokens' => 4000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);

        $result = json_decode($complete, true);

        if (isset($result['error'])) {
            throw new \Exception('Error from ChatGPT: ' . json_encode($result));
        }

        return $result;
    }

    // Input: messages array history
    // Return: new message [role=>?,content=?]
    public function ask($messages)
    {
        // try to get response message
        $result = $this->chat($messages);

        if (!isset($result['choices']) || !count($result['choices'])) {
            throw new \Exception('Can not find the answer from reponse: ' . json_encode($result));
        }

        return $result['choices'][0]['message'];
    }
}
