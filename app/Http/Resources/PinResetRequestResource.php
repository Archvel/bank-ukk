<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PinResetRequestResource extends JsonResource
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
            'saver_id' => $this->saver_id,
            'reset_token' => $this->reset_token,
            'status' => $this->status?->value ?? (string) $this->status,
            'verified_by' => $this->verified_by,
            'expires_at' => $this->expires_at?->toISOString(),
            'saver' => new SaverResource($this->whenLoaded('saver')),
            'verifier' => new UserResource($this->whenLoaded('verifier')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
