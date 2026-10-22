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
            'tipe_po'             => ['nullable', 'in:reguler,konsinyasi'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.id_produk'   => ['nullable', 'exists:products,id_produk'],
            'items.*.qty'         => ['required', 'integer', 'min:1'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.new_product' => ['required_without:items.*.id_produk', 'array'],
            'items.*.new_product.nama_produk'   => ['required_with:items.*.new_product', 'string', 'max:100'],
            'items.*.new_product.id_departemen' => ['required_with:items.*.new_product', 'exists:departments,id_departemen'],
            'items.*.new_product.id_subkelas'   => ['nullable', 'exists:subclasses,id_subkelas'],
            'items.*.new_product.sku'           => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'items.*.new_product.harga_jual'    => ['nullable', 'numeric', 'min:0'],
            'items.*.new_product.penerbit'      => ['nullable', 'string', 'max:150'],
            'notes'               => ['nullable', 'string'],
            'expected_date'       => ['nullable', 'date'],
        ];
    }
}