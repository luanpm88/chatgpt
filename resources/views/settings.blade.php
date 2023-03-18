@extends('layouts.core.backend')

@section('title', trans('chatgpt::messages.chatgpt'))

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li class="breadcrumb-item"><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ action("Admin\PaymentController@index") }}">{{ trans('messages.payment_gateways') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('messages.update') }}</li>
        </ul>
        <h1>
            <span class="text-semibold">
                <span class="material-symbols-rounded">
                    payments
                </span>
                {{ trans('chatgpt::messages.chatgpt') }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <h3 class="">{{ trans('chatgpt::messages.settings') }}</h3>
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
            <button class="btn btn-default me-1">{{ trans('messages.save') }}</button>
            <a class="btn btn-default" href="{{ action('Admin\PluginController@index') }}">{{ trans('cashier::messages.cancel') }}</a>
        </div>

    </form>
       
@endsection