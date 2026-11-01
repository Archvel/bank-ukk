<?php

namespace App\Models;

use App\Enums\SaverStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'member_id',
    'account_number',
    'pin',
    'qr_code_token',
    'balance',
    'status',
    'opened_at',
])]
#[Hidden(['pin'])]
class Saver extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'status' => SaverStatus::class,
            'opened_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function pinResetRequests(): HasMany
    {
        return $this->hasMany(PinResetRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SaverStatus::Active);
    }
}
