<?php

namespace App\Models;

use App\Enums\PinResetStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'saver_id',
    'reset_token',
    'status',
    'verified_by',
    'expires_at',
])]
class PinResetRequest extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PinResetStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    public function saver(): BelongsTo
    {
        return $this->belongsTo(Saver::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->status === PinResetStatus::Pending;
    }
}
