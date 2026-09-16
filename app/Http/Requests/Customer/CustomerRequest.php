<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
            'email' => 'nullable|email',
            'phone' => 'required|numeric',
            'address' => 'required|string'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Nama Pelanggan',
            'email' => 'Email Pelanggan',
            'phone' => 'Nomor Telepon Pelanggan',
            'address' => 'Alamat Pelanggan'
        ];
    }
}
