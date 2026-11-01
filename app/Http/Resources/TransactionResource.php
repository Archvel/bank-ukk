<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_number' => $this->transaction_number,
            'saver_id' => $this->saver_id,
            'teller_id' => $this->teller_id,
            'type' => $this->type?->value ?? (string) $this->type,
            'amount' => (int) $this->amount,
            'formatted_amount' => 'Rp'.number_format((int) $this->amount, 0, ',', '.'),
            'opening_balance' => (int) $this->opening_balance,
            'closing_balance' => (int) $this->closing_balance,
            'notes' => $this->notes,
            'saver' => new SaverResource($this->whenLoaded('saver')),
            'teller' => new UserResource($this->whenLoaded('teller')),
            'journal_entries' => JournalEntryResource::collection($this->whenLoaded('journalEntries')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
