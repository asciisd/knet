<?php


namespace Asciisd\Knet\Concerns;


trait ManagesEncryption
{
    private function encrypt($params)
    {
        return $this->encryptAES($params, config('knet.resource_key'));
    }

    private function encryptedParams()
    {
        $params = $this->setAsKeyAndValue($this->paramsToEncrypt);

        return $this->encrypt($params);
    }
}
