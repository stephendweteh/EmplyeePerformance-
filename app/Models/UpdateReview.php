<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UpdateReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_update_id',
        'reviewer_id',
        'rating',
        'comment',
        'status',
    ];

    public function employeeUpdate(): BelongsTo
    {
        return $this->belongsTo(EmployeeUpdate::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
