<?php

namespace App\Support;

use App\Models\PmbOfflineQueue;
use Illuminate\Database\Eloquent\Builder;

final class PmbOnlineSelectionAccess
{
    public static function latestOnlineUserIds()
    {
        return PmbOfflineQueue::query()
            ->where('selection_mode', PmbOfflineQueue::MODE_ONLINE)
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('pmb_offline_queues as newer_queues')
                    ->whereColumn('newer_queues.user_id', 'pmb_offline_queues.user_id')
                    ->where(function ($newer): void {
                        $newer->whereColumn('newer_queues.created_at', '>', 'pmb_offline_queues.created_at')
                            ->orWhere(function ($sameTime): void {
                                $sameTime->whereColumn('newer_queues.created_at', 'pmb_offline_queues.created_at')
                                    ->whereColumn('newer_queues.id', '>', 'pmb_offline_queues.id');
                            });
                    });
            })
            ->pluck('user_id');
    }

    public static function constrainToLatestOnlineUsers(Builder $query, string $userColumn = 'user_id'): Builder
    {
        return $query->whereIn($userColumn, self::latestOnlineUserIds());
    }

    public static function userHasLatestOnlineAssignment(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return (bool) PmbOfflineQueue::query()
            ->where('user_id', $userId)
            ->latest()
            ->latest('id')
            ->first()
            ?->isOnlineSelection();
    }

    public static function abortUnlessLatestOnlineUser(?int $userId): void
    {
        abort_unless(self::userHasLatestOnlineAssignment($userId), 404);
    }
}
