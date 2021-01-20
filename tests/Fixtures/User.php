<?php
namespace Asciisd\Knet\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Asciisd\Knet\HasKnet;

class User extends Authenticatable
{
    use HasKnet, Notifiable;
}
