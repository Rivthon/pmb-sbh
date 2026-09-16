<?php

namespace App\Listeners;

use App\Events\PmbStatusUpdated;
use App\Services\Audit\ActivityLogger;

class LogPmbStatusUpdated
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function handle(PmbStatusUpdated $event): void
    {
        $this->activityLogger->log(
            'pmb',
            'status.updated',
            'Status PMB mahasiswa diperbarui',
            $event->mahasiswa,
            ['status_pemb' => $event->oldStatus->value, 'label' => $event->oldStatus->label()],
            ['status_pemb' => $event->newStatus->value, 'label' => $event->newStatus->label()]
        );
    }
}
