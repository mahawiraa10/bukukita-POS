<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubclassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_subkelas' => 'required|string|max:255',
            'id_kelas' => 'required|exists:classes,id_kelas',
        ];
    }
}
