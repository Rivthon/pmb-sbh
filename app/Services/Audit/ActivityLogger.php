<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    public function log(
        string $module,
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): ?ActivityLog {
        try {
            $actor = Auth::guard('admin')->user() ?: Auth::guard('web')->user();
            $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('web')->check() ? 'web' : null);
            $request = request();

            return ActivityLog::create([
                'actor_type' => $actor ? $actor::class : null,
                'actor_id' => $actor?->getKey(),
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'guard' => $guard,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'old_values' => $this->redact($oldValues),
                'new_values' => $this->redact($newValues),
                'metadata' => $this->redact($metadata),
            ]);
        } catch (\Throwable $e) {
            Log::channel('audit')->warning('Failed writing activity log', [
                'module' => $module,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function redact(array $values): array
    {
        foreach (['password', 'password_confirmation', 'password_plaintext', 'remember_token', 'verification_code'] as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = '[redacted]';
            }
        }

        return $values;
    }
}
