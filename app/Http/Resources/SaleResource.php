<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_code'   => $this->invoice_code,
            'customer'       => $this->whenLoaded('customer', fn () => [
                'id'   => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'total_amount'   => (float) $this->total_amount,
            'discount'       => (float) $this->discount,
            'final_amount'   => (float) $this->final_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'sale_date'      => $this->sale_date->toIso8601String(),
            'items'          => SaleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
