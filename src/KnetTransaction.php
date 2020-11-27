<?php

namespace Asciisd\Knet;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static KnetTransaction create($params)
 * @method static KnetTransaction make($params)
 * @method static Builder where($search_key, $value)
 * @property string error_text
 * @property string paymentid
 * @property boolean paid
 * @property string result
 * @property string auth
 * @property string avr
 * @property string ref
 * @property string tranid
 * @property string postdate
 * @property string udf1
 * @property string udf2
 * @property string udf3
 * @property string udf4
 * @property string udf5
 * @property float amt
 * @property string error
 * @property string auth_resp_code
 * @property string trackid
 * @property string livemode
 * @property string url
 * @property integer user_id
 * @property Carbon created_at
 * @property string updated_at
 * @property Authenticatable owner
 * @property string card_number
 * @property string brand_id
 * @property string ip_address
 */
class KnetTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'error_text', 'paymentid', 'paid', 'result', 'auth', 'avr', 'ref', 'tranid', 'postdate', 'trackid',
        'udf1', 'udf2', 'udf3', 'udf4', 'udf5', 'amt', 'error', 'auth_resp_code', 'livemode', 'trackid', 'url',
        'card_number', 'brand_id', 'ip_address',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * get transaction from database by it's track id
     *
     * @param $trackId
     * @return KnetTransaction|Model
     */
    public static function findByTrackId($trackId)
    {
        return static::where('trackid', $trackId)->first();
    }

    /**
     * @param $paymentid
     * @return KnetTransaction|Model
     */
    public static function findByPaymentId($paymentid)
    {
        return static::where('paymentid', $paymentid)->first();
    }

    /**
     * @param $uuid
     * @return KnetTransaction|Model
     */
    public static function findByUuid($uuid)
    {
        return static::where('uuid', $uuid)->first();
    }

    public function owner()
    {
        $model = config('knet.model');

        return $this->belongsTo($model, (new $model)->getForeignKey());
    }

    public function isCaptured()
    {
        return $this->result == 'CAPTURED';
    }

    public function hasStatus()
    {
        return !empty($this->result);
    }
}
