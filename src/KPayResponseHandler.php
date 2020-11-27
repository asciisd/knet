<?php

namespace Asciisd\Knet;

use Illuminate\Support\Str;

class KPayResponseHandler extends KPayClient
{
    private $result = [];
    private $errors = [];
    private $error = null;
    private $error_code = '';

    public function __construct()
    {
        if (request()->exists('trandata')) {
            foreach ($this->decryptedData() as $datum) {
                $temp = explode('=', $datum);
                if (isset($temp[1])) {
                    if ($temp[0] == 'result') {
                        $temp[1] = implode(' ', explode('+', $temp[1]));
                        $this->result['paid'] = $temp[1] == 'CAPTURED';
                    }
                    $this->result[Str::snake($temp[0])] = $temp[1];
                }
            }
        } else {
            $this->result['error_text'] = request('ErrorText');
            $this->errors['error_text'] = request('ErrorText');

            $this->result['error'] = request('Error');
            $this->errors['error'] = request('Error');

            $this->result['paymentid'] = request('paymentid');
            $this->errors['paymentid'] = request('paymentid');

            $this->result['avr'] = request('avr');
            $this->errors['avr'] = request('avr');

            $this->result['result'] = 'FAILED';

            $this->error = request('ErrorText');
            $this->error_code = request('Error');
        }

        return $this;
    }

    public function __toString()
    {
        return json_encode($this->result);
    }

    public function toArray()
    {
        return $this->result;
    }

    public function errorsToArray()
    {
        return $this->result;
    }

    public function hasErrors()
    {
        return !is_null($this->error);
    }

    public function error()
    {
        return $this->hasErrors() ? $this->error : null;
    }

    public function errorCode()
    {
        return $this->hasErrors() ? $this->error_code : null;
    }

    public function isDuplicated()
    {
        return $this->error_code == 'IPAY0100114';
    }

    public function isInvalidPaymentStatus()
    {
        return $this->error_code == 'IPAY0100055';
    }

    private function decrypt($tranData)
    {
        return $this->decryptAES($tranData, config('knet.resource_key'));
    }

    private function decryptedData()
    {
        $tranData = request('trandata');
        return explode('&', $this->decrypt($tranData));
    }

    public function __get($name)
    {
        return $this->result[$name];
    }
}
