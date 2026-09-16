<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use App\Events\MahasiswaRegistered;
use App\Events\PaymentVerified;
use App\Events\PmbStatusUpdated;
use App\Events\StudentAccountGenerated;
use App\Listeners\LogMahasiswaRegistered;
use App\Listeners\LogPaymentVerified;
use App\Listeners\LogPmbStatusUpdated;
use App\Listeners\LogStudentAccountGenerated;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MahasiswaRegistered::class => [
            LogMahasiswaRegistered::class,
        ],
        PmbStatusUpdated::class => [
            LogPmbStatusUpdated::class,
        ],
        PaymentVerified::class => [
            LogPaymentVerified::class,
        ],
        StudentAccountGenerated::class => [
            LogStudentAccountGenerated::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
