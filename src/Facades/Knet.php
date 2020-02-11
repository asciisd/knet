<?php


namespace Asciisd\Knet\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Class Knet
 *
 * @method static \Asciisd\Knet\Knet make($amount, $options = [])
 *
 * @package Asciisd\Knet\Facades
 */
class Knet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'knet';
    }
}
