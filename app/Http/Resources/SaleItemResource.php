<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'id_produk' => $this->id_produk,
            'produk'    => $this->whenLoaded('product', fn () => $this->product?->nama_produk),
            'qty'       => $this->qty,
            'price'     => (float) $this->price,
            'subtotal'  => (float) $this->subtotal,
        ];
    }
}