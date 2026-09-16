<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
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
            'customer_id' => 'nullable|exists:customers,uuid',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,uuid',
            'items.*.price' => 'required|numeric|min:1',
            'items.*.quantity' => 'required|numeric|min:1',
            'total_payment' => 'required|numeric|min:1',
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'Customer',
            'notes' => 'Catatan',
            'items' => 'Data Barang',
            'items.*.item_id' => 'Barang',
            'items.*.quantity' => 'Jumlah',
            'items.*.price' => 'Harga',
            'total_payment' => 'Nominal Pembayaran'
        ];
    }
}
