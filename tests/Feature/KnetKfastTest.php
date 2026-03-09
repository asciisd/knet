<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\DataTransferObjects\PaymentRequest;
use Asciisd\Knet\Exceptions\KnetException;
use Asciisd\Knet\HasKnet;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\KPayClient;
use Asciisd\Knet\Services\KnetPaymentService;
use Asciisd\Knet\Tests\Mocks\KnetApiMock;
use Asciisd\Knet\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class KnetKfastTest extends TestCase
{
    private KnetPaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
        $this->setUpUsersTable();
        $this->paymentService = $this->app->make(KnetPaymentService::class);
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    private function createKfastUser(int $id = 1): User
    {
        return new class($id) extends User
        {
            use HasKnet;

            protected $fillable = ['id', 'name', 'email'];

            public function __construct(int $id = 1)
            {
                parent::__construct([
                    'id' => $id,
                    'name' => 'KFAST User',
                    'email' => 'kfast@example.com',
                ]);
            }
        };
    }

    public function test_kfast_token_generates_8_digit_padded_id()
    {
        $user = $this->createKfastUser(1);
        $this->assertEquals('00000001', $user->kfastToken());

        $user = $this->createKfastUser(42);
        $this->assertEquals('00000042', $user->kfastToken());

        $user = $this->createKfastUser(12345678);
        $this->assertEquals('12345678', $user->kfastToken());
    }

    public function test_kfast_token_throws_for_id_exceeding_8_digits()
    {
        $user = $this->createKfastUser(100000000);

        $this->expectException(KnetException::class);
        $this->expectExceptionMessage('User ID exceeds 8 digits');

        $user->kfastToken();
    }

    public function test_pay_with_kfast_sets_udf3_from_token()
    {
        $user = $this->createDbUser();
        KnetTransaction::useCustomerModel(User::class);

        $transaction = $this->paymentService->createPayment($user, 10.000, [
            'udf3' => str_pad((string) $user->id, 8, '0', STR_PAD_LEFT),
        ]);

        $this->assertEquals(str_pad((string) $user->id, 8, '0', STR_PAD_LEFT), $transaction->udf3);

        $url = $transaction->url;
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);

        $decrypted = KPayClient::decryptAES(
            urldecode($queryParams['trandata']),
            KnetApiMock::resourceKey()
        );
        parse_str($decrypted, $params);

        $this->assertEquals(str_pad((string) $user->id, 8, '0', STR_PAD_LEFT), $params['udf3']);
    }

    public function test_pay_with_kfast_allows_token_override_via_options()
    {
        $user = $this->createKfastUser(5);

        $this->assertEquals('00000005', $user->kfastToken());

        $overriddenToken = '99887766';
        $options = ['udf3' => $overriddenToken];
        $options['udf3'] = $options['udf3'] ?? $user->kfastToken();

        $this->assertEquals($overriddenToken, $options['udf3']);
    }

    public function test_invalid_udf3_format_throws_exception()
    {
        $user = new class extends User
        {
            protected $fillable = ['id', 'name', 'email'];
        };

        $this->expectException(KnetException::class);
        $this->expectExceptionMessage('KFAST token (udf3) must be exactly 8 numeric digits');

        new PaymentRequest(
            user: $user,
            amount: 10.000,
            udf3: 'invalid',
        );
    }

    public function test_udf3_with_less_than_8_digits_throws_exception()
    {
        $user = new class extends User
        {
            protected $fillable = ['id', 'name', 'email'];
        };

        $this->expectException(KnetException::class);
        $this->expectExceptionMessage('KFAST token (udf3) must be exactly 8 numeric digits');

        new PaymentRequest(
            user: $user,
            amount: 10.000,
            udf3: '1234567',
        );
    }

    public function test_udf3_with_more_than_8_digits_throws_exception()
    {
        $user = new class extends User
        {
            protected $fillable = ['id', 'name', 'email'];
        };

        $this->expectException(KnetException::class);
        $this->expectExceptionMessage('KFAST token (udf3) must be exactly 8 numeric digits');

        new PaymentRequest(
            user: $user,
            amount: 10.000,
            udf3: '123456789',
        );
    }

    public function test_udf3_with_non_numeric_chars_throws_exception()
    {
        $user = new class extends User
        {
            protected $fillable = ['id', 'name', 'email'];
        };

        $this->expectException(KnetException::class);
        $this->expectExceptionMessage('KFAST token (udf3) must be exactly 8 numeric digits');

        new PaymentRequest(
            user: $user,
            amount: 10.000,
            udf3: '1234abcd',
        );
    }

    public function test_null_udf3_passes_validation()
    {
        $user = new class extends User
        {
            protected $fillable = ['id', 'name', 'email'];
        };

        $request = new PaymentRequest(
            user: $user,
            amount: 10.000,
            udf3: null,
        );

        $this->assertNull($request->udf3);
    }

    public function test_valid_8_digit_udf3_passes_validation()
    {
        $user = new class extends User
        {
            protected $fillable = ['id', 'name', 'email'];
        };

        $request = new PaymentRequest(
            user: $user,
            amount: 10.000,
            udf3: '00000001',
        );

        $this->assertEquals('00000001', $request->udf3);
    }
}
