<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'computation_id',
        'detainee_id',
        'alert_level',
        'recommended_action',
        'assigned_to',
        'admin_override',
        'override_note',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'admin_override' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function computation(): BelongsTo
    {
        return $this->belongsTo(OverstayComputation::class, 'computation_id');
    }

    public function detainee(): BelongsTo
    {
        return $this->belongsTo(Detainee::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function legalActions(): HasMany
    {
        return $this->hasMany(LegalAction::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isFacilityStaff()) {
            return $query;
        }

        return $user->facility_id === null
            ? $query->whereRaw('1 = 0')
            : $query->whereHas(
                'detainee',
                fn (Builder $query) => $query->where('facility_id', $user->facility_id)
            );
    }

    /**
     * Get color class based on alert level.
     */
    public function getColorClassAttribute(): string
    {
        return match ($this->alert_level) {
            'critical' => 'red',
            'at_risk' => 'orange',
            'flagged' => 'yellow',
            'monitored' => 'blue',
            'resolved' => 'green',
            default => 'gray',
        };
    }
}
