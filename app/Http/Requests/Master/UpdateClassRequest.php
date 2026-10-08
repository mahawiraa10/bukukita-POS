<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_kelas' => 'sometimes|required|string|max:255',
            'id_departemen' => 'sometimes|required|exists:departments,id_departemen',
        ];
    }
}
