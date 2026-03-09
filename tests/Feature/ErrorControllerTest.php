<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\Tests\TestCase;

class ErrorControllerTest extends TestCase
{
    public function test_post_error_redirects_to_redirect_url()
    {
        $response = $this->post(route('knet.error'), [
            'error_text' => 'Payment failed',
        ]);

        $response->assertRedirect(config('knet.redirect_url'));
    }

    public function test_get_error_redirects_to_redirect_url()
    {
        $response = $this->get(route('knet.error.get', [
            'error_text' => 'Payment failed',
        ]));

        $response->assertRedirect(config('knet.redirect_url'));
    }

    public function test_error_flashes_error_data_to_session()
    {
        $response = $this->post(route('knet.error'), [
            'paymentid' => 'PAY123',
            'result' => 'FAILED',
            'error' => 'IPAY0100114',
            'error_text' => 'Duplicate Record',
        ]);

        $response->assertSessionHas('knet_error', function (array $errorData) {
            return $errorData['paymentid'] === 'PAY123'
                && $errorData['result'] === 'FAILED'
                && $errorData['error'] === 'IPAY0100114'
                && $errorData['error_text'] === 'Duplicate Record';
        });
    }

    public function test_error_flashes_validation_errors()
    {
        $response = $this->post(route('knet.error'), [
            'error_text' => 'Card declined by issuer',
        ]);

        $response->assertSessionHasErrors(['payment_error']);
    }

    public function test_error_uses_default_message_when_no_error_text()
    {
        $response = $this->post(route('knet.error'));

        $response->assertSessionHasErrors([
            'payment_error' => 'Payment processing failed',
        ]);
    }

    public function test_error_text_overridden_by_capital_error_text_param()
    {
        $response = $this->post(route('knet.error'), [
            'error_text' => 'Original error',
            'ErrorText' => 'Gateway error override',
        ]);

        $response->assertSessionHas('knet_error', function (array $errorData) {
            return $errorData['error_text'] === 'Gateway error override';
        });
    }

    public function test_error_code_overridden_by_capital_error_param()
    {
        $response = $this->post(route('knet.error'), [
            'error' => 'original_code',
            'Error' => 'IPAY0100055',
        ]);

        $response->assertSessionHas('knet_error', function (array $errorData) {
            return $errorData['error'] === 'IPAY0100055';
        });
    }

    public function test_get_error_with_query_params()
    {
        $response = $this->get(route('knet.error.get', [
            'error' => 'hex_validation_failed',
            'error_text' => 'Corrupted response data',
        ]));

        $response->assertRedirect(config('knet.redirect_url'));
        $response->assertSessionHas('knet_error');
    }
}
