<?php

namespace Asciisd\Knet\Tests\Integration;

use Asciisd\Knet\Tests\Fixtures\User;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class IntegrationTestCase extends TestCase
{
    protected static string $knetPrefix = 'knet-test-';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

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

    protected function createKnetResponse($trackid): array
    {
        return [
            "paymentid" => "100201933228935684",
            "trackid" => $trackid,
            "trandata" => "73E1277D4371801455853609824DFC3CBA655C414B10A20190AD3B778B5114B0CC572B7B29D7DBAF3297E1032F8C9B987465CA1FF103C162B17920424CB82697FBFF38CA82D289615788BD4065121E5333C10075671507FC05B3D34ACF0C47CE99B08C7C651022A370BD45BBD9C9F0B63D5B1392B54DBD1BAF4F1C79687C3D627A83508EF367E2EC87C911E1796061BFE423A7655F90ABE6249254A6F1BD1F761D63104322A18F3A0672A2E08007828E69340C42912A9908D8E328C281BD013DB9F464B55FA64252C62A0C6A7AEE5D3EF2C7E322552640E3170561906ED303D9D06066D9912C94D55D2102F4DBE63086",
        ];
    }
}
