<?php

namespace App\Models;

use App\Enums\TransactionType;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    /**
     * Generate the next unique daily transaction number using sequence ordering.
     */
    public static function generateNextNumber(?CarbonInterface $date = null): string
    {
        $targetDate = $date ? Carbon::instance($date) : now();
        $prefix = 'TRX-'.$targetDate->format('Ymd').'-';

        $latestNumber = static::query()
            ->where('transaction_number', 'like', $prefix.'%')
            ->orderByRaw('LENGTH(transaction_number) DESC')
            ->orderByDesc('transaction_number')
            ->lockForUpdate()
            ->value('transaction_number');

        $nextSequence = $latestNumber !== null
            ? ((int) Str::afterLast($latestNumber, '-') + 1)
            : 1;

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextSequence);
            $nextSequence++;
        } while (static::query()->where('transaction_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Aggregate transaction count and deposit/withdrawal totals using bound parameters.
     *
     * @return array{count: int, total_deposit: int, total_withdrawal: int}
     */
    public static function summarizeTotals(Builder $query): array
    {
        $row = (clone $query)
            ->selectRaw(
                'COUNT(*) as count, SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_deposit, SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_withdrawal',
                [TransactionType::Deposit->value, TransactionType::Withdrawal->value]
            )
            ->first();

        return [
            'count' => (int) ($row?->count ?? 0),
            'total_deposit' => (int) ($row?->total_deposit ?? 0),
            'total_withdrawal' => (int) ($row?->total_withdrawal ?? 0),
        ];
    }
}
