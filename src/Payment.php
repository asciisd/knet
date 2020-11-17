<?php


namespace Asciisd\Knet;

use Asciisd\Knet\Events\KnetTransactionCreated;
use Asciisd\Knet\Exceptions\PaymentActionRequired;
use Asciisd\Knet\Exceptions\PaymentFailure;
use Asciisd\Knet\Traits\Downloadable;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Str;

/**
 * Class Payment
 *
 * @property string result
 * @property integer amt
 * @property string url
 * @property string error_text
 * @property string paymentid
 * @property integer user_id
 * @property string udf1
 * @property string udf2
 * @property string udf3
 * @property string udf4
 * @property string udf5
 * @property string trackid
 *
 * @package Asciisd\Knet
 */
class Payment
{
    use Downloadable;

    const CURRENCY = 'KWD';
    const PROVIDER = 'KPay';

    /**
     * The Knet PaymentIntent instance.
     *
     * @var KnetTransaction
     */
    protected $transaction;

    /**
     * Create a new Payment instance.
     *
     * @param KnetTransaction $transaction
     */
    public function __construct(KnetTransaction $transaction)
    {
        $this->transaction = $transaction;

        KnetTransactionCreated::dispatch($transaction);
    }

    /**
     * Get the total amount that will be paid.
     *
     * @return string
     */
    public function amount()
    {
        return $this->rawAmount() . ' ' . self::CURRENCY;
    }

    /**
     * Get the raw total amount that will be paid.
     *
     * @return int
     */
    public function rawAmount()
    {
        return $this->transaction->amt;
    }

    /**
     * Determine if the payment needs an extra action like 3D Secure.
     *
     * @return bool
     */
    public function requiresAction()
    {
        return in_array($this->transaction->result, KPayResponseStatus::NEED_MORE_ACTION);
    }

    public function actionUrl()
    {
        return $this->transaction->url;
    }

    /**
     * Determine if the payment was cancelled.
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->transaction->result === KPayResponseStatus::CANCELLED;
    }

    /**
     * Determine if the payment was successful.
     *
     * @return bool
     */
    public function isSucceeded()
    {
        return in_array($this->transaction->result, KPayResponseStatus::SUCCESS_RESPONSES);
    }

    /**
     * Determine if the payment was failed.
     *
     * @return bool
     */
    public function isFailure()
    {
        return in_array($this->transaction->result, KPayResponseStatus::FAILED_RESPONSES);
    }

    public function receiptNo()
    {
        return $this->transaction->paymentid;
    }

    public function referenceNo()
    {
        return $this->transaction->ref ?? '00000000';
    }

    public function paymentMethod()
    {
        return 'Knet';
    }

    public function currency()
    {
        return self::CURRENCY;
    }

    public function paymentMethodIcon()
    {
        $method = Str::lower(Str::kebab($this->paymentMethod()));

        return url("vendor/knet/img/invoice/card/{$method}-dark@2x.png");
    }

    public function paymentMethodSvg()
    {
        $method = Str::lower(Str::kebab($this->paymentMethod()));

        return url("vendor/knet/img/invoice/card/svg/{$method}.svg");
    }

    public function status()
    {
        return $this->transaction->result;
    }

    public function statusIcon()
    {
        $status = Str::lower($this->status());
        return url("vendor/knet/img/invoice/status/{$status}.png");
    }

    /**
     * Validate if the payment intent was successful and throw an exception if not.
     *
     * @return void
     *
     * @throws PaymentActionRequired
     * @throws PaymentFailure
     */
    public function validate()
    {
        if ($this->isFailure()) {
            throw PaymentFailure::invalidPaymentMethod($this);
        } elseif ($this->requiresAction()) {
            throw PaymentActionRequired::incomplete($this);
        }
    }

    /**
     * Get a Carbon date for the invoice.
     *
     * @param DateTimeZone|string $timezone
     * @return Carbon
     */
    public function date($timezone = null)
    {
        $carbon = $this->transaction->created_at;

        return $timezone ? $carbon->setTimezone($timezone) : $carbon;
    }

    /**
     * The Knet model instance.
     *
     * @return Authenticatable
     */
    public function owner()
    {
        return $this->transaction->owner;
    }

    /**
     * The KnetTransaction instance.
     *
     * @return KnetTransaction
     */
    public function asKnetTransaction()
    {
        return $this->transaction;
    }

    /**
     * Dynamically get values from the PaymentIntent.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->transaction->{$key};
    }
}
