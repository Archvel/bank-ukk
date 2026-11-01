<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
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
            'entry_number' => $this->entry_number,
            'transaction_id' => $this->transaction_id,
            'cash_closure_id' => $this->cash_closure_id,
            'account_code' => $this->account_code,
            'account_name' => $this->account?->name,
            'type' => $this->type?->value ?? (string) $this->type,
            'amount' => (int) $this->amount,
            'formatted_amount' => 'Rp'.number_format((int) $this->amount, 0, ',', '.'),
            'description' => $this->description,
            'entry_date' => $this->entry_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
