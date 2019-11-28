<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Exceptions\KnetException;
use Illuminate\Support\Facades\App;
use Throwable;

class Knet extends KnetClient
{
    // hidden params
    private $id = null;
    private $password = null;
    private $action = null;
    private $langid = null;
    private $currencycode = null;
    private $responseURL = null;
    private $errorURL = null;
    private $amt = null;
    private $trackid = null;
    private $udf1 = null;
    private $udf2 = null;
    private $udf3 = null;
    private $udf4 = null;
    private $udf5 = null;

    // url params
    private $trandata = null;
    private $tranportalId = null;

    private $paramsToEncrypt = ['id', 'password', 'action', 'langid', 'currencycode', 'amt', 'responseURL', 'errorURL', 'trackid', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5'];
    protected $reqParams = ['trandata', 'tranportalId', 'responseURL', 'errorURL'];

    /**
     * Request constructor.
     *
     * @throws Throwable
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
     * check for existence of resource key
     *
     * @throws Throwable
     */
    private function checkForResourceKey()
    {
        if (config('knet.resource_key') == null) {
            throw KnetException::missingResourceKey();
        }
    }

    public function url()
    {
        return $this->getEnvUrl() . '&' . $this->urlParams();
    }

    private function getEnvUrl()
    {
        $url = config('knet.development_url');

        if (App::environment(['production'])) {
            $url = config('knet.production_url');
        }

        return $url . '?param=paymentInit';
    }

    private function setTranData()
    {
        $this->trandata = $this->encryptedParams();

        return $this;
    }

    public function setAmt($amount)
    {
        $this->amt = $amount;

        return $this;
    }

    public function setTrackId($trackid)
    {
        $this->trackid = $trackid;

        return $this;
    }

    public function setUDF1($param)
    {
        $this->udf1 = $param;

        return $this;
    }

    public function setUDF2($param)
    {
        $this->udf2 = $param;

        return $this;
    }

    public function setUDF3($param)
    {
        $this->udf3 = $param;

        return $this;
    }

    public function setUDF4($param)
    {
        $this->udf4 = $param;

        return $this;
    }

    public function setUDF5($param)
    {
        $this->udf5 = $param;

        return $this;
    }

    private function encryptedParams()
    {
        $params = $this->setAsKeyAndValue($this->paramsToEncrypt);

        return $this->encrypt($params);
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

    public function addTo($param, $key, $value)
    {
        if ($param === '') {
            $param .= "{$key}={$value}";
        } else {
            $param .= "&{$key}={$value}";
        }

        return $param;
    }

    private function encrypt($params)
    {
        return $this->encryptAES($params, config('knet.resource_key'));
    }
}
