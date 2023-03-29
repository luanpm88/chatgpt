@extends('layouts.core.backend', [
	'body_class' => 'has-topfix-header',
	'menu' => 'plugin',
])

@section('title', trans('chatgpt::messages.chatgpt'))

@section('page_header')

	<div class="topfix-header mb-4">
        <div style="display:flex;align-items:center;height: ;overflow:hidden;">
            <img src="{{ $chatgpt->getBannerUrl() }}" width="100%" />
        </div>
	</div>

@endsection

@section('content')
    <h2 class="">{{ trans('chatgpt::messages.connection') }}</h2>

    <div class="row">
        <div class="col-md-10">
            <p>
                {!! trans('chatgpt::messages.settings.intro') !!}
            </p>
        </div>
    </div>

    <form enctype="multipart/form-data" action="{{ action('\Acelle\Chatgpt\Controllers\ChatgptController@settings') }}" method="POST" class="form-validate-jquery">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('openai_api_key') ? 'has-error' : '' }}">
                    <label for="">{{ trans('chatgpt::messages.openai_api_key') }}</label>
                    <input
                        value="{{ config('app.demo') ? '*****************************' : $chatgpt->getOpenAIApiKey() }}"
                        type="password"
                        name="openai_api_key"
                        class="form-control required"
                    />
    
                    @if ($errors->has('openai_api_key'))
                        <span class="help-block">
                            {{ $errors->first('openai_api_key') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="text-left">
            @if (!$chatgpt->plugin->isActive())
                <input type="submit" name="enable" class="btn btn-primary me-1" value="{{ trans('chatgpt::messages.save_and_enable') }}" />
            @endif

            <button class="btn btn-secondary me-1">{{ trans('messages.save') }}</button>
            <a class="btn btn-light" href="{{ action('Admin\PluginController@index') }}">{{ trans('chatgpt::messages.cancel') }}</a>
        </div>

    </form>
       
@endsection