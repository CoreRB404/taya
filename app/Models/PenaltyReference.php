<?php

namespace App\Models;

use App\Support\DurationFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PenaltyReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'rpc_code',
        'charge_name',
        'max_penalty_years',
        'max_penalty_months',
        'law_source',
        'last_validated',
    ];

    protected function casts(): array
    {
        return [
            'max_penalty_years' => 'decimal:2',
            'last_validated' => 'date',
        ];
    }

    public function detainees(): HasMany
    {
        return $this->hasMany(Detainee::class, 'charge_rpc_code');
    }

    public function getPenaltyDurationDisplayAttribute(): string
    {
        return DurationFormatter::yearsMonthsToHuman($this->max_penalty_years, $this->max_penalty_months ?? 0);
    }
}
