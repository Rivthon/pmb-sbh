<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentAccountGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $mahasiswa,
        public readonly array $metadata = []
    ) {}
}
