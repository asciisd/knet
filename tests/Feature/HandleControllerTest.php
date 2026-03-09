<?php

namespace Asciisd\Knet\Tests\Feature;

use Asciisd\Knet\Tests\TestCase;

class HandleControllerTest extends TestCase
{
    public function test_handle_redirects_to_configured_redirect_url()
    {
        $response = $this->post(route('knet.handle'));

        $response->assertRedirect(config('knet.redirect_url'));
    }

    public function test_handle_redirects_to_custom_redirect_url()
    {
        config(['knet.redirect_url' => '/payment/complete']);

        $response = $this->post(route('knet.handle'));

        $response->assertRedirect('/payment/complete');
    }

    public function test_handle_returns_redirect_status()
    {
        $response = $this->post(route('knet.handle'));

        $response->assertStatus(302);
    }
}
