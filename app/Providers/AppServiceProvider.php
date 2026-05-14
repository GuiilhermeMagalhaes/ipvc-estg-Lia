<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $throttleRate = config('mail.throttleToMessagesPerMin');
        if ($throttleRate) {
        $throttlerPlugin = new \Swift_Plugins_ThrottlerPlugin($throttleRate, \Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE);
        Mail::getSwiftMailer()->registerPlugin($throttlerPlugin);
}
    }
}
