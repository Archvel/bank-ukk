<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'transaction_number',
    'saver_id',
    'teller_id',
    'type',
    'amount',
    'opening_balance',
    'closing_balance',
    'notes',
])]
class Transaction extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'integer',
            'opening_balance' => 'integer',
            'closing_balance' => 'integer',
        ];
    }

    public function saver(): BelongsTo
    {
        return $this->belongsTo(Saver::class)->withTrashed();
    }

    public function teller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeDeposit(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Deposit);
    }

    public function scopeWithdrawal(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Withdrawal);
    }
}
