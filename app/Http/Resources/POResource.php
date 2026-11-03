<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class POResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'po_code'       => $this->po_code,
            'id_pemasok'    => $this->id_pemasok,
            'tipe_po'       => $this->tipe_po ?? 'reguler',
            'vendor'        => $this->whenLoaded('vendor', fn () => $this->vendor?->nama_pemasok),
            'id_acara'      => $this->id_acara,
            'status'        => $this->status,
            'total_amount'  => (float) $this->total_amount,
            'payment_method' => $this->payment_method ?? 'CASH',
            'notes'         => $this->notes,
            'expected_date' => $this->expected_date?->format('Y-m-d'),
            'received_date' => $this->received_date?->format('Y-m-d'),
            'items'         => POItemResource::collection($this->whenLoaded('items')),
        ];
    }
}