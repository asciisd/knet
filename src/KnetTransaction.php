<?php

namespace Asciisd\Knet;

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
        'user_id', 'error_text', 'paymentid', 'paid', 'result', 'auth', 'avr', 'ref', 'tranid', 'postdate', 'trackid',
        'udf1', 'udf2', 'udf3', 'udf4', 'udf5', 'udf6', 'udf7', 'udf8', 'udf9', 'udf10', 'amt', 'error',
        'rspcode', 'livemode', 'trackid', 'url', 'card_number', 'brand_id', 'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * get transaction from database by its track id
     */
    public static function findByTrackId($trackId): KnetTransaction
    {
        return static::where('trackid', $trackId)->firstOrFail();
    }

    public static function findByPaymentId($paymentid): KnetTransaction
    {
        return static::where('paymentid', $paymentid)->firstOrFail();
    }

    public static function findByUuid($uuid): KnetTransaction
    {
        return static::where('uuid', $uuid)->firstOrFail();
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
}
