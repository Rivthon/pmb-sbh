<?php

namespace App\Listeners;

use App\Events\StudentAccountGenerated;
use App\Services\Audit\ActivityLogger;

class LogStudentAccountGenerated
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function handle(StudentAccountGenerated $event): void
    {
        $this->activityLogger->log(
            'pmb',
            'student-account.generated',
            'Akun mahasiswa berhasil digenerate',
            $event->mahasiswa,
            [],
            [],
            $event->metadata
        );
    }
}
