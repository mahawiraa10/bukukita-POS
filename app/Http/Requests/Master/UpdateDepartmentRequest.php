<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_departemen' => 'sometimes|required|string|max:255',
            'id_divisi' => 'sometimes|required|exists:divisions,id_divisi',
        ];
    }
}
