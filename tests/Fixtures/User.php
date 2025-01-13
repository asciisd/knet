<?php

namespace Asciisd\Knet\Tests\Fixtures;

use Asciisd\Knet\HasKnet;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasKnet, Notifiable;

    protected $guarded = [];
}
