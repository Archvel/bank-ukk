<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashClosureResource extends JsonResource
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
            'teller_id' => $this->teller_id,
            'closure_date' => $this->closure_date?->format('Y-m-d'),
            'opening_balance' => (int) $this->opening_balance,
            'total_deposit' => (int) $this->total_deposit,
            'total_withdrawal' => (int) $this->total_withdrawal,
            'system_ending_balance' => (int) $this->system_ending_balance,
            'physical_ending_balance' => (int) $this->physical_ending_balance,
            'difference' => (int) $this->difference,
            'is_balanced' => ((int) $this->difference === 0),
            'status' => $this->status?->value ?? (string) $this->status,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            'notes' => $this->notes,
            'teller' => new UserResource($this->whenLoaded('teller')),
            'approver' => new UserResource($this->whenLoaded('approver')),
            'journal_entries' => JournalEntryResource::collection($this->whenLoaded('journalEntries')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
