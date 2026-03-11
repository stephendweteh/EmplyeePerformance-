<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(?int $actorId, string $action, ?Model $subject = null, array $meta = []): void
    {
        ActivityLog::create([
            'actor_id' => $actorId,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'meta' => $meta,
        ]);
    }
}
