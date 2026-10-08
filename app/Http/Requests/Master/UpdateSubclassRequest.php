<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubclassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_subkelas' => 'sometimes|required|string|max:255',
            'id_kelas' => 'sometimes|required|exists:classes,id_kelas',
        ];
    }
}
