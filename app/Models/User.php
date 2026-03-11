<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'job_title',
        'bio',
        'profile_photo_path',
        'team_id',
        'role',
        'permissions',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public static function roles(): array
    {
        return ['employee', 'employer', 'admin', 'super_admin'];
    }

    public static function privileges(): array
    {
        return [
            'review_updates',
            'manage_teams',
            'publish_announcements',
            'export_reports',
            'view_activity_logs',
            'manage_users',
        ];
    }

    public static function defaultRolePrivileges(): array
    {
        return [
            'employee' => ['view_activity_logs'],
            'employer' => ['review_updates', 'manage_teams', 'publish_announcements', 'export_reports', 'view_activity_logs'],
            'admin' => ['review_updates', 'manage_teams', 'publish_announcements', 'export_reports', 'view_activity_logs', 'manage_users'],
            'super_admin' => self::privileges(),
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    public function updates(): HasMany
    {
        return $this->hasMany(EmployeeUpdate::class);
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(UpdateReview::class, 'reviewer_id');
    }

    public function announcementsAuthored(): HasMany
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    public function announcementReads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'actor_id');
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isEmployer(): bool
    {
        return in_array($this->role, ['employer', 'admin', 'super_admin'], true);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasPrivilege(string $privilege): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (is_array($this->permissions) && array_key_exists($privilege, $this->permissions)) {
            return (bool) $this->permissions[$privilege];
        }

        return in_array($privilege, self::defaultRolePrivileges()[$this->role] ?? [], true);
    }
}
