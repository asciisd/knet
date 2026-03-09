<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Events\KnetTransactionCreated;
use Asciisd\Knet\Events\KnetTransactionUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnetTransaction extends Model
{
    /**
     * The default customer model class name.
     */
    public static string $customerModel = 'App\\Models\\User';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'original_transaction_id', 'action', 'error_text', 'paymentid', 'paid', 'result', 'auth', 'avr',
        'ref', 'tranid', 'postdate', 'trackid', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5', 'udf6', 'udf7', 'udf8',
        'udf9', 'udf10', 'amt', 'error', 'rspcode', 'livemode', 'url', 'card_number', 'brand_id', 'ip_address',
        'refunded', 'refunded_at', 'refund_amount',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refunded' => 'boolean',
        'refund_amount' => 'float',
    ];

    protected $dispatchesEvents = [
        'created' => KnetTransactionCreated::class,
        'updated' => KnetTransactionUpdated::class,
    ];

    /**
     * @deprecated Use KnetTransactionRepository::findByTrackId() instead.
     */
    public static function findByTrackId($trackId): KnetTransaction
    {
        return static::where('trackid', $trackId)->firstOrFail();
    }

    /**
     * Set the customer model class name.
     */
    public static function useCustomerModel(string $customerModel): void
    {
        static::$customerModel = $customerModel;
    }

    public function owner(): BelongsTo
    {
        $model = static::$customerModel;

        return $this->belongsTo($model, (new $model)->getForeignKey());
    }

    public function isCaptured(): bool
    {
        return $this->result == 'CAPTURED';
    }

    public function hasStatus(): bool
    {
        return ! empty($this->result);
    }

    public function rawAmount(): float
    {
        return $this->amt;
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount, 3, '.', '');
    }

    public function isRefunded(): bool
    {
        return (bool) $this->refunded;
    }

    public function isRefundable(): bool
    {
        return $this->isCaptured() && ! $this->refunded;
    }
}
