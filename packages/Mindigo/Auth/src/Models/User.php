<?php

namespace Mindigo\Auth\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Mindigo\Profile\Models\NotificationPreference;
use Mindigo\RolePermission\Services\RolePermissionService;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Roles
     */
    const ROLES = [
        'admin' => 'Administrator',
        'teacher' => 'Teacher',
        'student' => 'Student',
    ];

    /**
     * Genders
     */
    const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ];

    /**
     * Mass assignable
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        'role',

        'phone',
        'avatar',
        'gender',
        'date_of_birth',
        'address',

        'bio',

        'is_active',

        'email_verified_at',
        'remember_token',
    ];

    /**
     * Hidden fields
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Appended attributes
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessors
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return $this->avatar
            ? asset('storage/'.$this->avatar)
            : $this->defaultAvatar();
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /**
     * Default avatar
     */
    private function defaultAvatar(): string
    {
        return 'https://ui-avatars.com/api/?name='.
            urlencode($this->name).
            '&background=2563eb&color=fff';
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', 'student');
    }

    public function scopeTeachers(Builder $query): Builder
    {
        return $query->where('role', 'teacher');
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', 'admin');
    }

    /**
     * Helpers
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    public function hasPermissionTo(string $permission): bool
    {
        return RolePermissionService::roleHasPermission($this->role, $permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }
}
