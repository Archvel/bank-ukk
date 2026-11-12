<?php

namespace App\Models;

use App\Enums\JournalEntryType;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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

    /**
     * Generate the next unique daily journal entry number using sequence ordering.
     */
    public static function generateNextNumber(?CarbonInterface $date = null): string
    {
        $targetDate = $date ? Carbon::instance($date) : now();
        $prefix = 'JRN-'.$targetDate->format('Ymd').'-';

        $latestNumber = static::query()
            ->where('entry_number', 'like', $prefix.'%')
            ->orderByRaw('LENGTH(entry_number) DESC')
            ->orderByDesc('entry_number')
            ->lockForUpdate()
            ->value('entry_number');

        $nextSequence = $latestNumber !== null
            ? ((int) Str::afterLast($latestNumber, '-') + 1)
            : 1;

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextSequence);
            $nextSequence++;
        } while (static::query()->where('entry_number', $candidate)->exists());

        return $candidate;
    }
}
