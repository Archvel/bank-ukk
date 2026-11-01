<?php

namespace App\Models;

use App\Enums\CashClosureStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'teller_id',
    'closure_date',
    'opening_balance',
    'total_deposit',
    'total_withdrawal',
    'system_ending_balance',
    'physical_ending_balance',
    'difference',
    'status',
    'approved_by',
    'approved_at',
    'notes',
])]
class CashClosure extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'closure_date' => 'date',
            'opening_balance' => 'integer',
            'total_deposit' => 'integer',
            'total_withdrawal' => 'integer',
            'system_ending_balance' => 'integer',
            'physical_ending_balance' => 'integer',
            'difference' => 'integer',
            'status' => CashClosureStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function teller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CashClosureStatus::Submitted);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CashClosureStatus::Approved);
    }
}
