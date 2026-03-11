<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeUpdateAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_update_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function employeeUpdate(): BelongsTo
    {
        return $this->belongsTo(EmployeeUpdate::class);
    }

    public function fileUrl(): string
    {
        return Storage::url($this->file_path);
    }
}
