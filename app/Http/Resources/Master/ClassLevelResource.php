<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassLevelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_kelas' => $this->id_kelas,
            'nama_kelas' => $this->nama_kelas,
            'id_departemen' => $this->id_departemen,
        ];
    }
}
