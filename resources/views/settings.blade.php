@extends('layouts.core.backend', [
	'body_class' => 'has-topfix-header',
	'menu' => 'plugin',
])

@section('title', trans('chatgpt::messages.chatgpt'))

@section('page_header')

	<div class="topfix-header">
        <div style="display:flex;align-items:center;height: ;overflow:hidden;">
            <img src="{{ $chatgpt->getBannerUrl() }}" width="100%" />
        </div>
	</div>

    <h2 class="mt-5">{{ trans('chatgpt::messages.connection') }}</h2>
    <p>
        {!! trans('chatgpt::messages.settings.intro') !!}
    </p>

    <form enctype="multipart/form-data" action="{{ action('\Acelle\Chatgpt\Controllers\ChatgptController@settings') }}" method="POST" class="form-validate-jquery">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-md-6">
                @include('helpers.form_control', [
                    'type' => 'text',
                    'class' => '',
                    'name' => 'openai_api_key',
                    'value' => $chatgpt->getOpenAIApiKey(),
                    'label' => trans('chatgpt::messages.openai_api_key'),
                    'help_class' => 'payment',
                    'rules' => ['openai_api_key' => 'required'],
                ])
            </div>
        </div>

        <div class="text-left">
            <button class="btn btn-secondary me-1">{{ trans('messages.save') }}</button>
            <a class="btn btn-default" href="{{ action('Admin\PluginController@index') }}">{{ trans('cashier::messages.cancel') }}</a>
        </div>

    </form>
       
@endsection