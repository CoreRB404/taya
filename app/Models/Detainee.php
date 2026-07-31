<?php

namespace App\Models;

use App\Support\DurationFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Detainee extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'charge_description',
        'charge_rpc_code',
        'commitment_date',
        'facility_id',
        'status',
        'tracking_code',
        'relative_name',
        'relative_phone',
        'relative_email',
        'tracking_enabled',
        'created_by',
        'bail_amount',
        'bail_status',
        'bail_posted_at',
        'bail_notes',
    ];

    protected function casts(): array
    {
        return [
            'commitment_date' => 'date',
            'bail_posted_at' => 'datetime',
            'tracking_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Detainee $detainee) {
            if (empty($detainee->tracking_code)) {
                $detainee->tracking_code = static::generateTrackingCode();
            }
        });
    }

    public static function generateTrackingCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $suffix = '';
            for ($i = 0; $i < 12; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = 'TAYA-'.$suffix;
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isFacilityStaff()) {
            return $query;
        }

        return $user->facility_id === null
            ? $query->whereRaw('1 = 0')
            : $query->where('facility_id', $user->facility_id);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function penaltyReference(): BelongsTo
    {
        return $this->belongsTo(PenaltyReference::class, 'charge_rpc_code');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(DetaineePhase::class);
    }

    public function overstayComputations(): HasMany
    {
        return $this->hasMany(OverstayComputation::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function legalActions(): HasMany
    {
        return $this->hasMany(LegalAction::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get days detained from commitment date to now.
     */
    public function getDaysDetainedAttribute(): int
    {
        return $this->commitment_date->diffInDays(Carbon::today());
    }

    public function getDaysDetainedDisplayAttribute(): string
    {
        return DurationFormatter::daysWithParenthetical($this->days_detained);
    }

    public function getBailStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', ucfirst($this->bail_status));
    }

    public function getBailAmountDisplayAttribute(): string
    {
        if ($this->bail_amount === null) {
            return 'Not set';
        }

        return '₱' . number_format($this->bail_amount, 0, '.', ',');
    }

    /**
     * Get the latest alert level for this detainee.
     */
    public function getLatestAlertAttribute(): ?Alert
    {
        return $this->alerts()->latest()->first();
    }
}
