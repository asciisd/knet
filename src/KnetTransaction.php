<?php

namespace Asciisd\Knet;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static KnetTransaction create($params)
 * @method static KnetTransaction make($params)
 * @method static Builder where($search_key, $value)
 * @property string result
 * @property integer amt
 * @property string currency
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
        'udf1', 'udf2', 'udf3', 'udf4', 'udf5', 'amt', 'error', 'auth_resp_code', 'livemode', 'trackid', 'url'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    public static function findByTrackId($trackId)
    {
        return static::where('trackid', $trackId)->first();
    }

    public function owner()
    {
        $model = config('knet.model');

        return $this->belongsTo($model, (new $model)->getForeignKey());
    }
}
