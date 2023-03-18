<?php

namespace Acelle\Chatgpt\Services;

use Illuminate\Support\Facades\Log;
use Acelle\Cashier\Interfaces\PaymentGatewayInterface;
use Carbon\Carbon;
use Acelle\Cashier\Cashier;
use Acelle\Model\Invoice;
use Acelle\Cashier\Library\TransactionVerificationResult;
use Acelle\Model\Transaction;
use Acelle\Chatgpt\Chatgpt;
use Acelle\Model\Plugin;

class ChatgptPaymentGateway implements PaymentGatewayInterface
{
    public $publicKey;
    public $secretKey;

    public const TYPE = 'chatgpt';

    /**
     * Construction
     */
    public function __construct($publicKey, $secretKey)
    {
        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;
    }

    public function getName() : string
    {
        return 'Chatgpt';
    }

    public function getType() : string
    {
        return self::TYPE;
    }

    public function getDescription() : string
    {
        return trans('chatgpt::messages.chatgpt.description');
    }

    public function getShortDescription() : string
    {
        return trans('chatgpt::messages.chatgpt.short_description');
    }

    public function isActive() : bool
    {
        return ($this->publicKey && $this->secretKey);
    }

    public function getSettingsUrl() : string
    {
        return action("\Acelle\Chatgpt\Controllers\ChatgptController@settings");
    }

    public function getCheckoutUrl($invoice) : string
    {
        return action("\Acelle\Chatgpt\Controllers\ChatgptController@checkout", [
            'invoice_uid' => $invoice->uid,
        ]);
    }

    public function verify(Transaction $transaction) : TransactionVerificationResult
    {
        $invoice = $transaction->invoice;
        $chatgpt = Chatgpt::initialize();

        return $chatgpt->runVerify($invoice);
    }
    

    public function allowManualReviewingOfTransaction() : bool
    {
        return false;
    }

    public function autoCharge($invoice)
    {
        $gateway = $this;
        $chatgpt = Chatgpt::initialize();

        $invoice->checkout($this, function($invoice) use ($chatgpt) {
            try {
                // charge invoice
                $chatgpt->pay($invoice);

                return new TransactionVerificationResult(TransactionVerificationResult::RESULT_DONE);
            } catch (\Exception $e) {
                return new TransactionVerificationResult(TransactionVerificationResult::RESULT_FAILED, $e->getMessage() .
                    '. <a href="' . $chatgpt->gateway->getCheckoutUrl($invoice) . '">Click here</a> to manually charge.');
            }
        });
    }

    public function getAutoBillingDataUpdateUrl($returnUrl='/') : string
    {
        return \Acelle\Cashier\Cashier::lr_action("\Acelle\Chatgpt\Controllers\ChatgptController@autoBillingDataUpdate", [
            'return_url' => $returnUrl,
        ]);
    }

    public function supportsAutoBilling() : bool
    {
        return false;
    }

    /**
     * Check if service is valid.
     *
     * @return void
     */
    public function test()
    {
        $chatgpt = Chatgpt::initialize();
        $chatgpt->test();
    }

    public function getMinimumChargeAmount($currency)
    {
        return 0;
    }
}
