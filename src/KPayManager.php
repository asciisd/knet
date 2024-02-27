<?php

namespace Asciisd\Knet;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Asciisd\Knet\Exceptions\KnetException;
use Asciisd\Knet\Concerns\ManagesEncryption;

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

    private $paramsToEncrypt = [
        'id', 'password', 'action', 'langid', 'currencycode', 'amt', 'responseURL', 'errorURL', 'trackid', 'udf1',
        'udf2', 'udf3', 'udf4', 'udf5',
    ];
    protected $reqParams = ['trandata', 'tranportalId', 'responseURL', 'errorURL'];

    /**
     * KPayManager constructor.
     *
     * @throws KnetException
     */
    public function __construct()
    {
        $this->checkForResourceKey();
        $this->initiatePaymentConfig();
    }

    private function initiatePaymentConfig()
    {
        $this->id           = config('knet.transport.id');
        $this->tranportalId = config('knet.transport.id');
        $this->password     = config('knet.transport.password');

        $this->action       = config('knet.action_code', 1);
        $this->langid       = config('knet.language', 'EN');
        $this->currencycode = config('knet.currency', 414);

        $this->responseURL = url(config('knet.response_url'));
        $this->errorURL    = url(config('knet.error_url'));
    }

    /**
     * @throws KnetException
     */
    private function fillPaymentWithOptions(array $options = []): self
    {
        if ( ! isset($options['amt'])) {
            throw KnetException::missingAmount();
        }

        if ( ! isset($options['trackid'])) {
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

    private function getEnvUrl(): string
    {
        $url = config('knet.development_url');

        if (App::environment(['production'])) {
            if ( ! env('KNET_DEBUG')) {
                $url = config('knet.production_url');
            }
        }

        return $url.'?param=paymentInit';
    }

    private static function getEnvInquiryUrl(): string
    {
        $url = config('knet.development_inquiry_url');

        if (App::environment(['production'])) {
            if ( ! env('KNET_DEBUG')) {
                $url = config('knet.production_inquiry_url');
            }
        }

        return $url.'?param=tranInit';
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

    private function setAsKeyAndValue($arrOfKeys): string
    {
        $params = '';

        foreach ($arrOfKeys as $param) {
            if ($this->{$param} != null) {
                $params = $this->addTo($params, $param, $this->{$param});
            }
        }

        return $params;
    }

    /**
     * @param $amount
     * @param  array  $options
     *
     * @return $this
     * @throws KnetException
     */
    public static function make($amount, array $options = []): static
    {
        $options['amt']     = $amount;
        $options['trackid'] = $options['trackid'] ?? Str::uuid();
        $options['result']  = $options['result'] ?? 'INITIATED';
        $options['user_id'] = $options['user_id'] ?? auth()->id();

        return (new self)->fillPaymentWithOptions($options);
    }

    /**
     * @throws KnetException
     */
    public static function inquiry($amt, $trackid): array
    {
        $paymentUrl = self::getEnvInquiryUrl();
        $xmlData = "<id>" .config('knet.transport.id') ."</id><password>" .config('knet.transport.password') ."</password><action>8</action><amt>" .$amt ."</amt><transid>" .$trackid ."</transid><udf5>" ."TrackID" ."</udf5><trackid>" .$trackid ."</trackid>";

        $ch = curl_init($paymentUrl);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: text/xml"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $output = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $xml = "<div>" . $output . "</div>";
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);

        $json = json_encode($xml);
        return json_decode($json, true); // true to have an array, false for an object
    }

    /**
     * @param $amount
     * @param $trackid
     *
     * @return $this
     * @throws KnetException
     */
    public static function refund($amount, $trackid)
    {
        throw new Exception('this method not yet supported by api');

        $options['amt']     = $amount;
        $options['trackid'] = $trackid;

        $new_instance         = (new self)->fillPaymentWithOptions($options);
        $new_instance->action = 2;

        return $new_instance;
    }

    /**
     * @throws KnetException
     */
    public static function void($amount, $trackid): self
    {
        throw new Exception('this method not yet supported by api');

        $options['amt']     = $amount;
        $options['trackid'] = $trackid;

        $new_instance         = (new self)->fillPaymentWithOptions($options);
        $new_instance->action = 3;

        return $new_instance;
    }

    public function url(): string
    {
        return $this->getEnvUrl().'&'.$this->urlParams();
    }

    public function inquiryUrl(): string
    {
        return 'https://kpaytest.com.kw/kpg/inquiry/PaymentHTTP.htm&'.$this->urlParams();
    }

    public function livemode(): bool
    {
        return App::environment(['production']) && ! env('KNET_DEBUG');
    }

    protected function addTo($param, $key, $value): string
    {
        if ($param === '') {
            $param .= "{$key}={$value}";
        } else {
            $param .= "&{$key}={$value}";
        }

        return $param;
    }

    public function toArray(): array
    {
        return [
            'trackid'  => $this->trackid,
            'livemode' => $this->livemode(),
            'result'   => $this->result,
            'user_id'  => $this->user_id,
            'amt'      => $this->amt,
            'url'      => $this->url(),
            'udf1'     => $this->udf1,
            'udf2'     => $this->udf2,
            'udf3'     => $this->udf3,
            'udf4'     => $this->udf4,
            'udf5'     => $this->udf5,
        ];
    }
}
