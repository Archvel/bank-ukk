<?php

namespace App\Models;

use App\Enums\JournalEntryType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_id',
    'cash_closure_id',
    'entry_number',
    'account_code',
    'type',
    'amount',
    'description',
    'entry_date',
])]
class JournalEntry extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => JournalEntryType::class,
            'amount' => 'integer',
            'entry_date' => 'date',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function cashClosure(): BelongsTo
    {
        return $this->belongsTo(CashClosure::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_code', 'code');
    }

    public function scopeDebit(Builder $query): Builder
    {
        return $query->where('type', JournalEntryType::Debit);
    }

    public function scopeCredit(Builder $query): Builder
    {
        return $query->where('type', JournalEntryType::Credit);
    }
}
