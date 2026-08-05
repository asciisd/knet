<?php

namespace Asciisd\Knet\Tests\Mocks;

use Asciisd\CashierCore\Contracts\CustomerContract;
use Illuminate\Foundation\Auth\User;

class CashierTestUser extends User implements CustomerContract
{
    protected $table = 'users';

    protected $fillable = ['id', 'name', 'email'];

    public function cashierId(): int|string
    {
        return $this->id ?? 1;
    }

    public function cashierEmail(): string
    {
        return $this->email ?? 'test@example.com';
    }

    public function cashierName(): string
    {
        return $this->name ?? 'Test User';
    }

    public function cashierLocale(): string
    {
        return 'en';
    }
}
