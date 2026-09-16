<?php

namespace App\Http\Requests\Profile;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users')->ignore(auth()->user()->uuid, 'uuid')
            ],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'password' => $this->strongPasswordRules(required: false),
            'address' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            'provinsi_id' => ['required', 'exists:provinsi,id_prov'],
            'kabupaten_id' => ['required', 'exists:kabupaten,id_kab'],
            'kecamatan_id' => ['required', 'exists:kecamatan,id_kec'],
            'kelurahan_id' => ['required', 'exists:kelurahan,id_kel'],
            'agama_id' => ['required', 'exists:agama,id'],
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'pek_ayah_id' => ['nullable', 'exists:pekerjaan_ayah,id'],
            'pek_ibu_id' => ['nullable', 'exists:pekerjaan_ibu,id'],
            'penghasilan_id' => ['nullable', 'exists:penghasilan_orang_tua,id'],
            'nisn' => ['required', 'digits:10'],
            'nik' => ['required', 'digits:16'],
            'no_telp_ortu' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'asal_sekolah' => ['nullable', 'string', 'max:255'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'string', 'in:L,P'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'email.email' => 'Format email tidak valid. Gunakan contoh nama@email.com.',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'phone.max' => 'Nomor HP/WhatsApp maksimal :max karakter.',
            'phone.regex' => 'Nomor HP/WhatsApp harus berisi 8-20 digit/karakter nomor yang valid.',
            'no_telp_ortu.regex' => 'Nomor telepon orang tua harus berisi 8-20 digit/karakter nomor yang valid.',
            'address.max' => 'Alamat lengkap maksimal :max karakter.',
            'nik.digits' => 'NIK wajib terdiri dari 16 digit angka sesuai KTP/KK.',
            'nisn.digits' => 'NISN wajib terdiri dari 10 digit angka.',
            'jenis_kelamin.in' => 'Jenis kelamin hanya boleh Laki-laki atau Perempuan.',
            'exists' => 'Pilihan :attribute tidak valid. Silakan pilih dari daftar yang tersedia.',
            'password.min' => 'Password minimal :min karakter.',
            'password.regex' => 'Password wajib berisi huruf besar, huruf kecil, angka, dan simbol.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'image' => ':Attribute harus berupa gambar.',
            'mimes' => 'Format :attribute harus JPG, JPEG, atau PNG.',
            'max' => ':Attribute tidak boleh lebih dari :max.',
            'date' => ':Attribute harus berupa tanggal yang valid.',
            'before' => ':Attribute harus sebelum hari ini.',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'phone' => 'Nomor HP',
            'password' => 'Password',
            'address' => 'Alamat',
            'image' => 'Foto Profil',
            'provinsi_id' => 'Provinsi',
            'kabupaten_id' => 'Kabupaten',
            'kecamatan_id' => 'Kecamatan',
            'kelurahan_id' => 'Kelurahan',
            'agama_id' => 'Agama',
            'jurusan_id' => 'Program Studi',
            'pek_ayah_id' => 'Pekerjaan Ayah',
            'pek_ibu_id' => 'Pekerjaan Ibu',
            'penghasilan_id' => 'Penghasilan Orang Tua',
            'nisn' => 'NISN',
            'nik' => 'NIK',
            'no_telp_ortu' => 'Nomor Telepon Orang Tua',
            'asal_sekolah' => 'Asal Sekolah',
            'nama_ayah' => 'Nama Ayah',
            'nama_ibu' => 'Nama Ibu',
            'nama_wali' => 'Nama Wali',
            'tempat_lahir' => 'Tempat Lahir',
            'tgl_lahir' => 'Tanggal Lahir',
            'status_biodata' => 'Status',
            'jenis_kelamin' =>   'Jenis Kelamin',
        ];
    }

    private function strongPasswordRules(bool $required): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:8',
            'regex:/[A-Z]/',
            'regex:/[a-z]/',
            'regex:/[0-9]/',
            'regex:/[^A-Za-z0-9]/',
            'confirmed',
        ];
    }
}
