<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function actorLabel(): string
    {
        $name = $this->actor?->name ?? 'System';

        if (! $this->actor) {
            return $name;
        }

        if ($this->actor->isEmployee()) {
            return 'Employee '.$name;
        }

        return 'Manager '.$name;
    }

    public function plainActionText(): string
    {
        $meta = is_array($this->meta) ? $this->meta : [];

        return match ($this->action) {
            'employee_update.created' => $this->actorLabel().' submitted a daily update.',
            'employee_update.updated' => $this->actorLabel().' updated a daily update.',
            'update_review.saved' => $this->actorLabel().' submitted a review'.(isset($meta['rating']) ? ' (rating '.$meta['rating'].'/10).' : '.'),
            'update_review.updated' => $this->actorLabel().' updated a review'.(isset($meta['rating']) ? ' (rating '.$meta['rating'].'/10).' : '.'),
            'announcement.published' => $this->actorLabel().' published a live update.',
            'announcement.updated' => $this->actorLabel().' edited a live update.',
            'announcement.deleted' => $this->actorLabel().' removed a live update.',
            'announcement.read' => $this->actorLabel().' marked a live update as read.',
            default => $this->actorLabel().' '.str_replace(['.', '_'], [' ', ' '], $this->action).'.',
        };
    }

    public function plainMetaLines(): array
    {
        $meta = is_array($this->meta) ? $this->meta : [];
        $lines = [];

        foreach ($meta as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            $label = ucwords(str_replace('_', ' ', (string) $key));
            $lines[] = $label.': '.$value;
        }

        return $lines;
    }
}
