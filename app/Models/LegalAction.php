<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class LegalAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_id',
        'detainee_id',
        'action_type',
        'filed_by',
        'notes',
        'filed_at',
    ];

    protected function casts(): array
    {
        return [
            'filed_at' => 'datetime',
        ];
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function detainee(): BelongsTo
    {
        return $this->belongsTo(Detainee::class);
    }

    public function filedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }
}
