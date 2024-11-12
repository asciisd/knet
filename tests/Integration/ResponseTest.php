<?php


namespace Asciisd\Knet\Tests\Integration;


use Asciisd\Knet\Exceptions\PaymentActionRequired;

class ResponseTest extends IntegrationTestCase
{
    /** @test */
    public function response_can_handle_the_success_income_results()
    {
        $user = $this->createCustomer('customer_can_be_charged');

        $this->expectException(PaymentActionRequired::class);

        $payment = $user->pay(10);

        $this->assertDatabaseHas('knet_transactions', [
            'trackid' => $payment->trackid
        ]);

        $header = ['referer' => ['https://kpaytest.com.kw/kpg/paymentrouter.htm']];
        $body = $this->createKnetResponse($payment->trackid);
        $response = $this->post('knet/response', $body, $header);

        $response->assertRedirect(config('knet.redirect_url'));
    }
}
