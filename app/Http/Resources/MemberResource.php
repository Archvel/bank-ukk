<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'student_name' => $this->student_name,
            'graduation_year' => $this->graduation_year,
            'account_status' => $this->account_status?->value ?? (string) $this->account_status,
            'user' => new UserResource($this->whenLoaded('user')),
            'saver' => new SaverResource($this->whenLoaded('saver')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
