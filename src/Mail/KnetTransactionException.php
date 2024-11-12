<?php

namespace Asciisd\Knet\Mail;

use Asciisd\Knet\KnetTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

class KnetTransactionException extends Mailable
{
    use Queueable, SerializesModels;

    protected KnetTransaction $transaction;
    protected string $error_message;

    /**
     * Create a new message instance.
     *
     * @param  KnetTransaction  $transaction
     * @param  String  $error_message
     */
    public function __construct(KnetTransaction $transaction, string $error_message)
    {
        $this->transaction = $transaction;
        $this->error_message = $error_message;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->subject("Knet Order #{$this->transaction->paymentid}")
            ->html(
                (new MailMessage)
                    ->greeting('Hello Support Team,')
                    ->line("Knet portal return this payment with following error:-")
                    ->line('---------------------------------')
                    ->line($this->error_message)
                    ->line('---------------------------------')
                    ->line('Please check this transaction on kpay portal for more information')
                    ->action('Visit KPay Portal', 'https://www.kpay.com.kw/portal/InstOrderList.htm')
                    ->line('Thank you for using our application!')
                    ->error()
                    ->render()
            );
    }
}
