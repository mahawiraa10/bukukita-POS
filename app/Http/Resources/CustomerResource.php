<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'city'          => $this->city,
            'gender'        => $this->gender,
            'total_spent'   => (float) $this->total_spent,
            'points'        => $this->points,
            'is_member'     => (bool) $this->is_member,
            'member_since'  => $this->member_since?->format('Y-m-d'),
        ];
    }
}
