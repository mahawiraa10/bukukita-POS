<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class SubclassResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_subkelas' => $this->id_subkelas,
            'nama_subkelas' => $this->nama_subkelas,
            'id_kelas' => $this->id_kelas,
        ];
    }
}
