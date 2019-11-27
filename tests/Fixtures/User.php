<?php
namespace Asciisd\Knet\Tests\Fixtures;
use Illuminate\Foundation\Auth\User as Model;
use Illuminate\Notifications\Notifiable;
use Asciisd\Knet\HasKnet;

class User extends Model
{
    use HasKnet, Notifiable;
}