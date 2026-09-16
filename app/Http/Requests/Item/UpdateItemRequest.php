<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'item_category_id' => 'required|exists:item_categories,uuid',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:150',
            'hpp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Nama Barang',
            'item_category_id' => 'Kategori Barang',
            'stock' => 'Stok Barang',
            'unit' => 'Satuan',
            'hpp' => 'Harga Beli (HPP)',
            'selling_price' => 'Harga Jual'
        ];
    }
}
