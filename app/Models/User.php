<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'facility_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        // Keep legacy MFA data private until the deployed database columns are
        // removed in a separately reviewed schema cleanup.
        'mfa_code_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function assignedAlerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'assigned_to');
    }

    public function legalActions(): HasMany
    {
        return $this->hasMany(LegalAction::class, 'filed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::STAFF->value;
    }

    public function isFacilityStaff(): bool
    {
        return $this->role === UserRole::STAFF->value;
    }

    public function isLawyer(): bool
    {
        return $this->hasRole(UserRole::LAWYER->value, UserRole::AUTHORIZED_USER->value);
    }

    public function isAuditor(): bool
    {
        return $this->role === UserRole::AUDITOR->value;
    }

    public function canManageOperations(): bool
    {
        return $this->isAdmin() || $this->isStaff();
    }

    public function canAccessFacility(?int $facilityId): bool
    {
        if ($this->isFacilityStaff()) {
            return $this->facility_id !== null && $this->facility_id === $facilityId;
        }

        return true;
    }

}
