<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'wins',
        'business_impact',
        'blockers',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'tags' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(UpdateReview::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmployeeUpdateAttachment::class);
    }
}
