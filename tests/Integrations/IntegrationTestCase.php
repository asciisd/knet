<?php


namespace Asciisd\Knet\Tests\Integrations;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Asciisd\Knet\Tests\Fixtures\User;
use Asciisd\Knet\Tests\TestCase;

class IntegrationTestCase extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Eloquent::unguard();
        $this->loadLaravelMigrations();
        $this->artisan('migrate')->run();
    }

    protected function createCustomer($description = 'aemaddin'): User
    {
        return User::create([
            'email' => "{$description}@knet-test.com",
            'name' => 'Amr Ahmed',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ]);
    }
}