<?php

namespace Asciisd\Knet\Tests\Unit;

use Asciisd\Knet\Enums\PaymentStatus;
use Asciisd\Knet\Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_success_statuses_are_successful()
    {
        $this->assertTrue(PaymentStatus::SUCCESS->isSuccessful());
        $this->assertTrue(PaymentStatus::CAPTURED->isSuccessful());
    }

    public function test_failed_statuses_are_not_successful()
    {
        $this->assertFalse(PaymentStatus::FAILED->isSuccessful());
        $this->assertFalse(PaymentStatus::ABANDONED->isSuccessful());
        $this->assertFalse(PaymentStatus::CANCELLED->isSuccessful());
        $this->assertFalse(PaymentStatus::DECLINED->isSuccessful());
        $this->assertFalse(PaymentStatus::RESTRICTED->isSuccessful());
        $this->assertFalse(PaymentStatus::VOID->isSuccessful());
        $this->assertFalse(PaymentStatus::TIMEDOUT->isSuccessful());
        $this->assertFalse(PaymentStatus::NOT_CAPTURED->isSuccessful());
    }

    public function test_pending_statuses_are_not_successful()
    {
        $this->assertFalse(PaymentStatus::PENDING->isSuccessful());
        $this->assertFalse(PaymentStatus::UNKNOWN->isSuccessful());
        $this->assertFalse(PaymentStatus::INITIATED->isSuccessful());
    }

    public function test_is_paid_mirrors_is_successful()
    {
        $this->assertTrue(PaymentStatus::CAPTURED->isPaid());
        $this->assertTrue(PaymentStatus::SUCCESS->isPaid());
        $this->assertFalse(PaymentStatus::FAILED->isPaid());
        $this->assertFalse(PaymentStatus::PENDING->isPaid());
    }

    public function test_is_failed_returns_true_for_failure_statuses()
    {
        $failedStatuses = [
            PaymentStatus::FAILED,
            PaymentStatus::ABANDONED,
            PaymentStatus::CANCELLED,
            PaymentStatus::DECLINED,
            PaymentStatus::RESTRICTED,
            PaymentStatus::VOID,
            PaymentStatus::TIMEDOUT,
            PaymentStatus::UNKNOWN,
            PaymentStatus::NOT_CAPTURED,
        ];

        foreach ($failedStatuses as $status) {
            $this->assertTrue($status->isFailed(), "{$status->value} should be failed");
        }
    }

    public function test_is_failed_returns_false_for_non_failure_statuses()
    {
        $this->assertFalse(PaymentStatus::SUCCESS->isFailed());
        $this->assertFalse(PaymentStatus::CAPTURED->isFailed());
        $this->assertFalse(PaymentStatus::PENDING->isFailed());
        $this->assertFalse(PaymentStatus::INITIATED->isFailed());
    }

    public function test_is_pending_returns_true_for_pending_statuses()
    {
        $this->assertTrue(PaymentStatus::PENDING->isPending());
        $this->assertTrue(PaymentStatus::INITIATED->isPending());
    }

    public function test_is_pending_returns_false_for_non_pending_statuses()
    {
        $this->assertFalse(PaymentStatus::CAPTURED->isPending());
        $this->assertFalse(PaymentStatus::FAILED->isPending());
        $this->assertFalse(PaymentStatus::UNKNOWN->isPending());
    }

    public function test_need_more_action_mirrors_is_pending()
    {
        $this->assertTrue(PaymentStatus::PENDING->needMoreAction());
        $this->assertTrue(PaymentStatus::INITIATED->needMoreAction());
        $this->assertFalse(PaymentStatus::CAPTURED->needMoreAction());
    }

    public function test_display_name_returns_human_readable_strings()
    {
        $this->assertEquals('Success', PaymentStatus::SUCCESS->displayName());
        $this->assertEquals('Captured', PaymentStatus::CAPTURED->displayName());
        $this->assertEquals('Failed', PaymentStatus::FAILED->displayName());
        $this->assertEquals('Abandoned', PaymentStatus::ABANDONED->displayName());
        $this->assertEquals('Cancelled', PaymentStatus::CANCELLED->displayName());
        $this->assertEquals('Declined', PaymentStatus::DECLINED->displayName());
        $this->assertEquals('Restricted', PaymentStatus::RESTRICTED->displayName());
        $this->assertEquals('Void', PaymentStatus::VOID->displayName());
        $this->assertEquals('Timedout', PaymentStatus::TIMEDOUT->displayName());
        $this->assertEquals('Unknown', PaymentStatus::UNKNOWN->displayName());
        $this->assertEquals('Not Captured', PaymentStatus::NOT_CAPTURED->displayName());
        $this->assertEquals('Pending', PaymentStatus::PENDING->displayName());
        $this->assertEquals('Initiated', PaymentStatus::INITIATED->displayName());
    }

    public function test_style_color_returns_correct_css_class()
    {
        $this->assertEquals('success-status', PaymentStatus::SUCCESS->styleColor());
        $this->assertEquals('success-status', PaymentStatus::CAPTURED->styleColor());
        $this->assertEquals('info-status', PaymentStatus::INITIATED->styleColor());
        $this->assertEquals('info-status', PaymentStatus::UNKNOWN->styleColor());
        $this->assertEquals('info-status', PaymentStatus::PENDING->styleColor());
        $this->assertEquals('danger-status', PaymentStatus::FAILED->styleColor());
        $this->assertEquals('danger-status', PaymentStatus::CANCELLED->styleColor());
    }

    public function test_text_color_returns_correct_value()
    {
        $this->assertEquals('successText', PaymentStatus::CAPTURED->textColor());
        $this->assertEquals('infoText', PaymentStatus::PENDING->textColor());
        $this->assertEquals('dangerText', PaymentStatus::FAILED->textColor());
    }

    public function test_bg_color_returns_correct_value()
    {
        $this->assertEquals('successBG', PaymentStatus::CAPTURED->bgColor());
        $this->assertEquals('infoBG', PaymentStatus::PENDING->bgColor());
        $this->assertEquals('dangerBG', PaymentStatus::FAILED->bgColor());
    }

    public function test_success_states_returns_enum_names()
    {
        $states = PaymentStatus::successStates();

        $this->assertContains('SUCCESS', $states);
        $this->assertContains('CAPTURED', $states);
        $this->assertCount(2, $states);
    }

    public function test_success_states_values_returns_enum_values()
    {
        $values = PaymentStatus::successStatesValues();

        $this->assertContains('SUCCESS', $values);
        $this->assertContains('CAPTURED', $values);
        $this->assertCount(2, $values);
    }

    public function test_failed_states_returns_failure_names()
    {
        $states = PaymentStatus::failedStates();

        $this->assertContains('ABANDONED', $states);
        $this->assertContains('CANCELLED', $states);
        $this->assertContains('FAILED', $states);
        $this->assertContains('DECLINED', $states);
        $this->assertContains('RESTRICTED', $states);
        $this->assertContains('VOID', $states);
        $this->assertContains('TIMEDOUT', $states);
        $this->assertContains('NOT_CAPTURED', $states);
        $this->assertCount(8, $states);
    }

    public function test_loading_states_returns_pending_names()
    {
        $states = PaymentStatus::loadingStates();

        $this->assertContains('INITIATED', $states);
        $this->assertContains('UNKNOWN', $states);
        $this->assertContains('PENDING', $states);
        $this->assertCount(3, $states);
    }

    public function test_to_full_array_returns_complete_status_data()
    {
        $data = PaymentStatus::CAPTURED->toFullArray();

        $this->assertEquals('CAPTURED', $data['id']);
        $this->assertEquals('Captured', $data['name']);
        $this->assertEquals('success-status', $data['style']);
        $this->assertEquals('successText', $data['text_color']);
        $this->assertEquals('successBG', $data['bg_color']);
    }

    public function test_image_url_returns_status_based_path()
    {
        $url = PaymentStatus::CAPTURED->imageUrl();

        $this->assertStringContainsString('vendor/knet/img/invoice/status/captured.png', $url);
    }

    public function test_can_create_from_value()
    {
        $status = PaymentStatus::from('CAPTURED');
        $this->assertSame(PaymentStatus::CAPTURED, $status);

        $status = PaymentStatus::from('NOT CAPTURED');
        $this->assertSame(PaymentStatus::NOT_CAPTURED, $status);
    }

    public function test_try_from_returns_null_for_invalid_value()
    {
        $this->assertNull(PaymentStatus::tryFrom('INVALID_STATUS'));
    }
}
