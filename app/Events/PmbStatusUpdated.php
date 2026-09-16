<?php

namespace App\Events;

use App\Enums\PmbStatus;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PmbStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $mahasiswa,
        public readonly PmbStatus $oldStatus,
        public readonly PmbStatus $newStatus
    ) {}
}
