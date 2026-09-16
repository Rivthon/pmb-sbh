<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'email' => ['required', 'email:rfc', 'unique:users,email'],
            'kuesioner_id' => 'required|exists:kuesioners,id',
            'jurusan_id' => 'required|exists:jurusan,id',
            'terms' => 'accepted',

            // ✅ Tambahan baru
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor HP/WhatsApp harus berisi 8-20 digit/karakter nomor yang valid.',
            'email.email' => 'Format email tidak valid. Gunakan email aktif, contoh nama@email.com.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.',
            'terms.accepted' => 'Anda wajib menyetujui syarat dan kebijakan pendaftaran.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Siswa',
            'phone' => 'No Telp Siswa',
            'email' => 'Email',
            'kuesioner_id' => 'Kuesioner',
            'jurusan_id' => 'Jurusan',
            'terms' => 'Syarat & Kebijakan',
        ];
    }
}
