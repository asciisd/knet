<?php

namespace Asciisd\Knet\Providers;

use Asciisd\Knet\Knet;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Your application and company details.
     */
    protected array $details = [];

    /**
     * All the application developer e-mail addresses.
     */
    protected array $developers = [];

    /**
     * The address where customer support e-mails should be sent.
     */
    protected ?string $sendSupportEmailsTo = null;

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Knet::details($this->details);
        Knet::sendSupportEmailsTo($this->sendSupportEmailsTo);

        if (count($this->developers) > 0) {
            Knet::developers($this->developers);
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }
}
