<?php

namespace Asciisd\Knet;

use Illuminate\Support\Str;

class KnetResponseHandler extends KnetClient
{
    private $result = [];
    private $error = [];
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

    public function hasErrors()
    {
        return count($this->error);
    }

    public function error()
    {
        return $this->hasErrors() ? $this->error : null;
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
