<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Concerns\ManagesEncryption;
use Asciisd\Knet\Exceptions\KnetException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class KPayManager extends KPayClient
{
    use ManagesEncryption;

    protected $id = null;
    protected $password = null;
    protected $action = null;
    protected $langid = null;
    protected $currencycode = null;
    protected $responseURL = null;
    protected $errorURL = null;
    protected $amt = null;
    protected $trackid = null;
    protected $udf1 = null;
    protected $udf2 = null;
    protected $udf3 = null;
    protected $udf4 = null;
    protected $udf5 = null;
    protected $user_id = null;
    protected $result = null;

    // url params
    protected $trandata = null;
    protected $tranportalId = null;

    private $paramsToEncrypt = ['id', 'password', 'action', 'langid', 'currencycode', 'amt', 'responseURL', 'errorURL', 'trackid', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5'];
    protected $reqParams = ['trandata', 'tranportalId', 'responseURL', 'errorURL'];

    /**
     * KPayManager constructor.
     * @throws KnetException
     */
    public function __construct()
    {
        $this->checkForResourceKey();
        $this->initiatePaymentConfig();
    }

    private function initiatePaymentConfig()
    {
        $this->id = config('knet.transport.id');
        $this->tranportalId = config('knet.transport.id');
        $this->password = config('knet.transport.password');

        $this->action = config('knet.action_code');
        $this->langid = config('knet.language');
        $this->currencycode = config('knet.currency');

        $this->responseURL = url(config('knet.response_url'));
        $this->errorURL = url(config('knet.error_url'));
    }

    /**
     * @param array $options
     * @throws KnetException
     */
    private function fillPaymentWithOptions(array $options = [])
    {
        if (!isset($options['amt'])) {
            throw KnetException::missingAmount();
        }

        if (!isset($options['trackid'])) {
            throw KnetException::missingTrackId();
        }

        foreach ($options as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }

        return $this;
    }

    /**
     * check for existence of resource key
     *
     * @throws KnetException
     */
    private function checkForResourceKey()
    {
        if (config('knet.resource_key') == null) {
            throw KnetException::missingResourceKey();
        }
    }

    private function getEnvUrl()
    {
        $url = config('knet.development_url');

        if (App::environment(['production'])) {
            if (!env('KNET_DEBUG')) {
                $url = config('knet.production_url');
            }
        }

        return $url . '?param=paymentInit';
    }

    private function setTranData()
    {
        $this->trandata = $this->encryptedParams();

        return $this;
    }

    private function urlParams()
    {
        $this->setTranData();

        return $this->setAsKeyAndValue($this->reqParams);
    }

    private function setAsKeyAndValue($arrOfKeys)
    {
        $params = '';

        foreach ($arrOfKeys as $param) {
            if ($this->{$param} != null)
                $params = $this->addTo($params, $param, $this->{$param});
        }

        return $params;
    }

    /**
     * @param $amount
     * @param array $options
     * @return $this
     * @throws KnetException
     */
    public static function make($amount, $options = [])
    {
        $options['amt'] = $amount;
        $options['trackid'] = $options['trackid'] ?? Str::uuid();
        $options['result'] = $options['result'] ?? 'INITIATED';
        $options['user_id'] = $options['user_id'] ?? auth()->id();

        return (new self)->fillPaymentWithOptions($options);
    }

    public function url()
    {
        return $this->getEnvUrl() . '&' . $this->urlParams();
    }

    public function livemode()
    {
        return App::environment(['production']) && !env('KNET_DEBUG');
    }

    protected function addTo($param, $key, $value)
    {
        if ($param === '') {
            $param .= "{$key}={$value}";
        } else {
            $param .= "&{$key}={$value}";
        }

        return $param;
    }

    public function toArray()
    {
        return [
            'trackid' => $this->trackid,
            'livemode' => $this->livemode(),
            'result' => $this->result,
            'user_id' => $this->user_id,
            'amt' => $this->amt,
            'url' => $this->url(),
            'udf1' => $this->udf1,
            'udf2' => $this->udf2,
            'udf3' => $this->udf3,
            'udf4' => $this->udf4,
            'udf5' => $this->udf5,
        ];
    }
}
