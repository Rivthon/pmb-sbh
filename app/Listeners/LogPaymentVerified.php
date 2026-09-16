<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use App\Services\Audit\ActivityLogger;

class LogPaymentVerified
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function handle(PaymentVerified $event): void
    {
        $this->activityLogger->log(
            'payment',
            'payment.verified',
            'Pembayaran mahasiswa PMB diverifikasi',
            $event->mahasiswa,
            [],
            $event->paymentData
        );
    }
}
