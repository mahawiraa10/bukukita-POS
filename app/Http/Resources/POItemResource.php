<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class POItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'id_produk'    => $this->id_produk,
            'produk'       => $this->whenLoaded('product', fn () => $this->product?->nama_produk),
            'qty'          => $this->qty,
            'unit_price'   => (float) $this->unit_price,
            'subtotal'     => (float) $this->subtotal,
            'received_qty' => $this->received_qty,
        ];
    }
}