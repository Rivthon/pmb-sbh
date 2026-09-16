<?php

namespace App\Listeners;

use App\Events\MahasiswaRegistered;
use App\Services\Audit\ActivityLogger;

class LogMahasiswaRegistered
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function handle(MahasiswaRegistered $event): void
    {
        $this->activityLogger->log(
            'pmb',
            'mahasiswa.registered',
            'Mahasiswa PMB terdaftar',
            $event->mahasiswa,
            [],
            $event->mahasiswa->only(['id', 'name', 'email', 'phone', 'jurusan_id', 'periode_id', 'gelombang_id'])
        );
    }
}
