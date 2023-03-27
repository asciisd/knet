<?php

namespace Asciisd\Knet\Providers;

use Asciisd\Knet\Knet;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Your application and company details.
     *
     * @var array
     */
    protected $details = [];

    /**
     * All the application developer e-mail addresses.
     *
     * @var array
     */
    protected $developers = [];

    /**
     * The address where customer support e-mails should be sent.
     *
     * @var string
     */
    protected $sendSupportEmailsTo = null;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Knet::details($this->details);
        Knet::sendSupportEmailsTo($this->sendSupportEmailsTo);

        if (count($this->developers) > 0) {
            Knet::developers($this->developers);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
