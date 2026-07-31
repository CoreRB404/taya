<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
        'address',
        'capacity',
    ];

    public function detainees(): HasMany
    {
        return $this->hasMany(Detainee::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getActiveDetaineeCountAttribute(): int
    {
        return $this->detainees()->where('status', 'active')->count();
    }

    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->capacity <= 0) {
            return 0.0;
        }

        return min(100.0, ($this->active_detainee_count / $this->capacity) * 100);
    }

    public function getIsOverCapacityAttribute(): bool
    {
        return $this->capacity > 0 && $this->active_detainee_count > $this->capacity;
    }
}
