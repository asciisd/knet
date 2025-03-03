<?php

namespace Asciisd\Knet;

use Asciisd\Knet\Config\KnetConfig;
use Asciisd\Knet\Exceptions\KnetException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class KPayManager extends KPayClient
{
    protected ?int $id = null;
    protected ?string $password = null;
    protected ?string $action = null;
    protected ?string $langid = null;
    protected ?string $currencycode = null;
    protected ?string $responseURL = null;
    protected ?string $errorURL = null;
    protected ?string $amt = null;
    protected ?string $trackid = null;
    protected ?string $udf1 = null;
    protected ?string $udf2 = null;
    protected ?string $udf3 = null;
    protected ?string $udf4 = null;
    protected ?string $udf5 = null;
    protected ?int $user_id = null;
    protected ?string $result = null;

    // url params
    protected ?string $trandata = null;
    protected ?string $tranportalId = null;
    protected array $reqParams = ['trandata', 'tranportalId', 'responseURL', 'errorURL'];
    private array $paramsToEncrypt = [
        'id', 'password', 'action', 'langid', 'currencycode', 'amt', 'responseURL', 'errorURL', 'trackid', 'udf1',
        'udf2', 'udf3', 'udf4', 'udf5',
    ];

    private readonly KnetConfig $config;

    /**
     * KPayManager constructor.
     */
    public function __construct(KnetConfig $config)
    {
        $this->config = $config;
        $this->initiatePaymentConfig();
    }

    private function initiatePaymentConfig(): void
    {
        $this->id = $this->config->getTransportId();
        $this->tranportalId = $this->config->getTransportId();
        $this->password = $this->config->getTransportPassword();

        $this->action = config('knet.action_code', 1);
        $this->langid = config('knet.language', 'EN');
        $this->currencycode = config('knet.currency', 414);

        $this->responseURL = url(config('knet.response_url'));
        $this->errorURL = url(config('knet.error_url'));
    }

    /**
     * Create a new payment instance
     */
    public static function make($amount, array $options = []): static
    {
        $options['amt'] = $amount;
        $options['trackid'] = $options['trackid'] ?? Str::uuid();
        $options['result'] = $options['result'] ?? 'INITIATED';
        $options['user_id'] = $options['user_id'] ?? auth()->id();

        return app(static::class)->fillPaymentWithOptions($options);
    }

    private function fillPaymentWithOptions(array $options = []): self
    {
        if (! isset($options['amt'])) {
            throw KnetException::missingAmount();
        }

        if (! isset($options['trackid'])) {
            throw KnetException::missingTrackId();
        }

        foreach ($options as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }

        return $this;
    }

    public static function inquiry($amt, $trackid): array
    {
        $paymentUrl = self::getEnvInquiryUrl();
        $xmlData = "<id>".config('knet.transport.id')."</id><password>".config('knet.transport.password')."</password><action>8</action><amt>".$amt."</amt><transid>".$trackid."</transid><udf5>"."TrackID"."</udf5><trackid>".$trackid."</trackid>";

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

        $xml = "<div>".$output."</div>";
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
        $xml = simplexml_load_string($xml);

        $json = json_encode($xml);
        return json_decode($json, true); // true to have an array, false for an object
    }

    private static function getEnvInquiryUrl(): string
    {
        $url = config('knet.development_inquiry_url');

//        if (App::environment(['production'])) {
            if (! config('knet.debug')) {
                $url = config('knet.production_inquiry_url');
            }
//        }

        return $url.'?param=tranInit';
    }

    /**
     * @throws \Exception
     */
    public static function refund($amount, $trackid)
    {
        throw new \Exception('this method not yet supported by api');
    }

    /**
     * @throws \Exception
     */
    public static function void($amount, $trackid): self
    {
        throw new \Exception('this method not yet supported by api');
    }

    public function inquiryUrl(): string
    {
        return 'https://kpaytest.com.kw/kpg/inquiry/PaymentHTTP.htm&'.$this->urlParams();
    }

    private function urlParams(): string
    {
        $this->setTranData();
        return $this->setAsKeyAndValue($this->reqParams);
    }

    private function setTranData(): void
    {
        $this->trandata = $this->encryptedParams();
    }

    private function encryptedParams(): string
    {
        $params = $this->setAsKeyAndValue($this->paramsToEncrypt);
        return $this->encrypt($params);
    }

    private function encrypt(string $params): string
    {
        return $this->encryptAES($params, $this->config->getResourceKey());
    }

    protected function setAsKeyAndValue($arrOfKeys): string
    {
        $params = '';
        foreach ($arrOfKeys as $param) {
            if ($this->{$param} != null) {
                $params = $this->addTo($params, $param, $this->{$param});
            }
        }
        return $params;
    }

    protected function addTo($param, $key, $value): string
    {
        if ($param === '') {
            return "{$key}={$value}";
        }
        return $param."&{$key}={$value}";
    }

    public function toArray(): array
    {
        return [
            'trackid' => $this->trackid,
            'livemode' => !$this->config->isDebugMode(),
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

    public function url(): string
    {
        return $this->config->getPaymentUrl().'?param=paymentInit&'.$this->urlParams();
    }
}
