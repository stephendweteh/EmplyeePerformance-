<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return PublicStorage::url($this->file_path);
    }
}
