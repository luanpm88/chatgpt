<?php

namespace Acelle\Chatgpt\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller as BaseController;
use Acelle\Model\Plugin;
use Acelle\Model\Invoice;
use Acelle\Chatgpt\Chatgpt;
use Acelle\Library\Facades\Billing;
use Acelle\Cashier\Library\TransactionVerificationResult;

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

            if ($request->enable_gateway) {
                $chatgpt->plugin->activate();
                Billing::enablePaymentGateway($chatgpt->gateway->getType());
            }

            if ($chatgpt->plugin->isActive()) {
                return redirect()->action("Admin\PaymentController@index")
                    ->with('alert-success', trans('cashier::messages.gateway.updated'));
            } else {
                return redirect()->action("Admin\PluginController@index")
                    ->with('alert-success', trans('cashier::messages.gateway.updated'));
            }
        }

        return view('chatgpt::settings', [
            'chatgpt' => $chatgpt,
        ]);
    }

    public function checkout(Request $request, $invoice_uid)
    {        
        $invoice = Invoice::findByUid($invoice_uid);
        $chatgpt = Chatgpt::initialize($invoice);
        
        // Save return url
        if ($request->return_url) {
            $request->session()->put('checkout_return_url', $request->return_url);
        }

        // exceptions
        if (!$invoice->isNew()) {
            throw new \Exception('Invoice is not new');
        }

        // free plan. No charge
        if ($invoice->total() == 0) {
            $invoice->checkout($chatgpt->gateway, function($invoice) {
                return new TransactionVerificationResult(TransactionVerificationResult::RESULT_DONE);
            });

            return redirect()->action('SubscriptionController@index');
        }

        // use new card
        if ($request->transaction_id) {
            // save checkout information
            $chatgpt->updateData($invoice, [
                'checkout' => $request->all(),
            ]);
            
            try {
                // check pay
                $result = $chatgpt->runVerify($invoice);

                $invoice->checkout($chatgpt->gateway, function($invoice) use ($result) {
                    return $result;
                });

                return redirect()->action('SubscriptionController@index');
            } catch (\Exception $e) {
                // return with error message
                $request->session()->flash('alert-error', $e->getMessage());
                return redirect()->action('SubscriptionController@index');
            }
        }

        // use old card
        if ($request->isMethod('post')) {
            // Use current card
            if ($request->current_card) {
                try {
                    // charge invoice
                    $chatgpt->gateway->autoCharge($invoice);

                    return redirect()->action('SubscriptionController@index');
                } catch (\Exception $e) {
                    // invoice checkout
                    $invoice->checkout($chatgpt->gateway, function($invoice) use ($e) {
                        return new TransactionVerificationResult(TransactionVerificationResult::RESULT_FAILED, $e->getMessage());
                    });
                    return redirect()->action('SubscriptionController@index');
                }
            }
        }

        return view('chatgpt::checkout', [
            'chatgpt' => $chatgpt,
            'invoice' => $invoice,
        ]);
    }
}
