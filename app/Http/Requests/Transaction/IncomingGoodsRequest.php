<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class IncomingGoodsRequest extends FormRequest
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
            'supplier_id' => 'nullable|exists:suppliers,uuid',
            'incoming_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,uuid',
            'items.*.total_stock' => 'required|numeric|min:1'
        ];
    }

    public function attributes(): array
    {
        return [
            'supplier_id' => 'Supplier',
            'incoming_date' => 'Tanggal',
            'notes' => 'Catatan',
            'items' => 'Data Barang',
            'items.*.item_id' => 'Barang',
            'items.*.total_stock' => 'Jumlah'
        ];
    }
}
