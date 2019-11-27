<?php


namespace Asciisd\Knet;


class Payment
{

    const CAPTURED = 'Captured';
    const NOT_CAPTURED = 'Not Captured';


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
    }

    /**
     * Get the total amount that will be paid.
     *
     * @return string
     */
    public function amount()
    {
        return $this->rawAmount() . ' ' . $this->transaction->currency;
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
     * Determine if the payment was cancelled.
     *
     * @return bool
     */
    public function isCaptured()
    {
        return $this->transaction->result === self::CAPTURED;
    }

    /**
     * Determine if the payment was successful.
     *
     * @return bool
     */
    public function isNonCaptured()
    {
        return $this->transaction->result === self::NOT_CAPTURED;
    }

    public function customer()
    {
        return $this->transaction->user();
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