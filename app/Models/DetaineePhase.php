<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DetaineePhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'detainee_id',
        'phase_number',
        'phase_name',
        'due_date',
        'day_count',
        'completed',
        'completed_at',
        'completed_by',
        'flagged',
        'flag_reason',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed' => 'boolean',
            'completed_at' => 'datetime',
            'flagged' => 'boolean',
        ];
    }

    public function detainee(): BelongsTo
    {
        return $this->belongsTo(Detainee::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Check if phase is overdue (past due date and not completed).
     */
    public function getIsOverdueAttribute(): bool
    {
        return !$this->completed && $this->due_date->isPast();
    }

    /**
     * Get days overdue (0 if not overdue).
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }
}
