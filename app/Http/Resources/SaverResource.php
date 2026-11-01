<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaverResource extends JsonResource
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
            'user_id' => $this->user_id,
            'member_id' => $this->member_id,
            'account_number' => $this->account_number,
            'qr_code_token' => $this->qr_code_token,
            'balance' => (int) $this->balance,
            'formatted_balance' => 'Rp'.number_format((int) $this->balance, 0, ',', '.'),
            'status' => $this->status?->value ?? (string) $this->status,
            'opened_at' => $this->opened_at?->toISOString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'member' => new MemberResource($this->whenLoaded('member')),
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
