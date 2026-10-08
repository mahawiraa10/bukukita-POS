<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_departemen' => 'required|string|max:255',
            'id_divisi' => 'required|exists:divisions,id_divisi',
        ];
    }
}
