<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_kelas' => 'required|string|max:255',
            'id_departemen' => 'required|exists:departments,id_departemen',
        ];
    }
}
