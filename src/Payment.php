<?php


namespace Asciisd\Knet;

use Asciisd\Knet\Events\KnetTransactionCreated;

/**
 * Class Payment
 *
 * @property string result
 * @property integer amt
 * @property string currency
 * @property string url
 * @property string error_text
 * @property string paymentid
 * @property integer user_id
 * @property string udf1
 * @property string udf2
 * @property string udf3
 * @property string udf4
 * @property string udf5
 *
 * @package Asciisd\Knet
 */
class Payment
{
    const CAPTURED = 'Captured';
    const NOT_CAPTURED = 'Not Captured';
    const PENDING = 'Pending';
    const CURRENCY = 'KD';

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
     * Determine if the payment was successful.
     *
     * @return bool
     */
    public function isCaptured()
    {
        return $this->transaction->result === self::CAPTURED;
    }

    /**
     * Determine if the payment was failed.
     *
     * @return bool
     */
    public function isFailed()
    {
        return $this->transaction->result === self::NOT_CAPTURED;
    }

    /**
     * Determine if the payment was pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->transaction->result === self::PENDING;
    }

    /**
     * Determine if the payment was failed.
     *
     * @return bool
     */
    public function isNotCaptured()
    {
        return $this->transaction->result === self::NOT_CAPTURED;
    }

    public function customer()
    {
        return $this->transaction->owner;
    }

    public function url()
    {
        return $this->transaction->url;
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
