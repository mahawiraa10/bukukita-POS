<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePORequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_pemasok'          => ['required', 'exists:vendors,id_pemasok'],
            'id_acara'            => ['nullable', 'exists:events,id_acara'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.id_produk'   => ['required', 'exists:products,id_produk'],
            'items.*.qty'         => ['required', 'integer', 'min:1'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string'],
            'expected_date'       => ['nullable', 'date'],
        ];
    }
}