<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_departemen' => $this->id_departemen,
            'nama_departemen' => $this->nama_departemen,
            'id_divisi' => $this->id_divisi,
        ];
    }
}
