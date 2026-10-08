<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceivePORequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'               => ['required', 'array', 'min:1'],
            'items.*.id'          => ['required', 'exists:po_items,id'],
            'items.*.received_qty'=> ['required', 'integer', 'min:1'],
        ];
    }
}