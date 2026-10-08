<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class DivisionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_divisi' => $this->id_divisi,
            'nama_divisi' => $this->nama_divisi,
        ];
    }
}
