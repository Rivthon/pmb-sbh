<?php

namespace App\Http\Requests\Admin;

use App\Enums\PmbStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateMahasiswaBaruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return $this->createRules();
        }

        return $this->updateRules();
    }

    /**
     * Rules applied on Create (POST).
     */
    private function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                'unique:users,email',
            ],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'periode_id' => ['required', 'exists:periodes,id'],
            'gelombang_id' => [
                'required',
                'exists:gelombangs,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->input('periode_id')) {
                        $exists = \DB::table('gelombangs')
                            ->where('id', $value)
                            ->where('periode_id', $this->input('periode_id'))
                            ->exists();
                        if (!$exists) {
                            $fail('Gelombang yang dipilih tidak sesuai dengan Periode Akademik yang dipilih.');
                        }
                    }
                }
            ],
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'kuesioner_id' => ['nullable', 'exists:kuesioners,id'],
            'provinsi_id' => ['required', 'exists:provinsi,id_prov'],
            'kabupaten_id' => ['required', 'exists:kabupaten,id_kab'],
            'kecamatan_id' => ['required', 'exists:kecamatan,id_kec'],
            'kelurahan_id' => ['required', 'exists:kelurahan,id_kel'],
            'agama_id' => ['required', 'exists:agama,id'],
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
            'status_biodata' => ['nullable', 'integer', 'in:0,1'],
            'status_berkas' => ['nullable', 'integer', 'in:0,1,2'],
            'status_pemb' => ['nullable', 'integer', Rule::in($this->pmbStatusValues())],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_ktp' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_kk' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_ijazah' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_bukti' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'password' => $this->strongPasswordRules(required: true),
        ];
    }

    /**
     * Rules applied on Update (PUT/PATCH).
     */
    private function updateRules(): array
    {
        $userId = $this->route('mahasiswa_baru')
            ?? $this->route('mahasiswa')
            ?? $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'periode_id' => ['required', 'exists:periodes,id'],
            'gelombang_id' => [
                'required',
                'exists:gelombangs,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->input('periode_id')) {
                        $exists = \DB::table('gelombangs')
                            ->where('id', $value)
                            ->where('periode_id', $this->input('periode_id'))
                            ->exists();
                        if (!$exists) {
                            $fail('Gelombang yang dipilih tidak sesuai dengan Periode Akademik yang dipilih.');
                        }
                    }
                }
            ],
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'kuesioner_id' => ['nullable', 'exists:kuesioners,id'],
            'provinsi_id' => ['required', 'exists:provinsi,id_prov'],
            'kabupaten_id' => ['required', 'exists:kabupaten,id_kab'],
            'kecamatan_id' => ['required', 'exists:kecamatan,id_kec'],
            'kelurahan_id' => ['required', 'exists:kelurahan,id_kel'],
            'agama_id' => ['required', 'exists:agama,id'],
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
            'status_biodata' => ['nullable', 'integer', 'in:0,1'],
            'status_berkas' => ['nullable', 'integer', 'in:0,1,2'],
            'status_pemb' => ['nullable', 'integer', Rule::in($this->pmbStatusValues())],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_ktp' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_kk' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_ijazah' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_bukti' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'password' => $this->strongPasswordRules(required: false),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama mahasiswa wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'periode_id.required' => 'Periode wajib dipilih.',
            'gelombang_id.required' => 'Gelombang wajib dipilih.',
            'jurusan_id.required' => 'Program studi wajib dipilih.',
            'phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor HP/WhatsApp harus berisi 8-20 digit/karakter nomor yang valid.',
            'no_telp_ortu.regex' => 'Nomor telepon orang tua harus berisi 8-20 digit/karakter nomor yang valid.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            
            'required' => 'Kolom :attribute wajib diisi.',
            'string' => 'Kolom :attribute harus berupa teks.',
            'max' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
            'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
            'unique' => ':Attribute sudah terdaftar di sistem.',
            'exists' => ':Attribute yang dipilih tidak valid.',
            'digits' => 'Kolom :attribute harus terdiri dari :digits digit angka.',
            'date' => 'Kolom :attribute harus berupa tanggal yang valid.',
            'before' => 'Kolom :attribute harus sebelum hari ini.',
            'regex' => 'Format kolom :attribute tidak valid.',
            'min' => 'Kolom :attribute minimal harus :min karakter.',
            'password.regex' => 'Kata sandi wajib berisi huruf besar, huruf kecil, angka, dan simbol.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'image' => 'Kolom :attribute harus berupa gambar.',
            'mimes' => 'Format berkas :attribute harus :values.',
            'in' => 'Pilihan kolom :attribute tidak valid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $statusPemb = $this->filled('status_pemb')
                ? (int) $this->input('status_pemb')
                : PmbStatus::BelumUpdateBerkas->value;

            if ($statusPemb < PmbStatus::Verified->value) {
                return;
            }

            $mahasiswa = $this->existingMahasiswa();
            $statusBiodata = $this->filled('status_biodata')
                ? (int) $this->input('status_biodata')
                : (int) ($mahasiswa?->status_biodata ?? 1);
            $statusBerkas = $this->filled('status_berkas')
                ? (int) $this->input('status_berkas')
                : (int) ($mahasiswa?->status_berkas ?? 0);

            if ($statusBiodata !== 1) {
                $validator->errors()->add('status_pemb', 'Pembayaran/status PMB tidak boleh diverifikasi sebelum biodata lengkap.');
            }

            if ($statusBerkas !== 1 || !$this->hasRequiredDocuments($mahasiswa)) {
                $validator->errors()->add('status_pemb', 'Pembayaran/status PMB tidak boleh diverifikasi sebelum berkas wajib lengkap.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'phone' => 'nomor HP',
            'periode_id' => 'periode akademik',
            'gelombang_id' => 'gelombang PMB',
            'jurusan_id' => 'program studi',
            'nisn' => 'NISN',
            'nik' => 'NIK',
            'tgl_lahir' => 'tanggal lahir',
            'tempat_lahir' => 'tempat lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'address' => 'alamat lengkap',
            'agama_id' => 'agama',
            'pek_ayah_id' => 'pekerjaan ayah',
            'pek_ibu_id' => 'pekerjaan ibu',
            'penghasilan_id' => 'penghasilan orang tua',
            'no_telp_ortu' => 'nomor telepon orang tua',
            'asal_sekolah' => 'asal sekolah',
            'nama_ayah' => 'nama ayah',
            'nama_ibu' => 'nama ibu',
            'nama_wali' => 'nama wali',
            'password' => 'kata sandi',
            'image' => 'foto profil',
            'img_ktp' => 'dokumen KTP',
            'img_kk' => 'dokumen Kartu Keluarga',
            'img_ijazah' => 'dokumen Ijazah / SKL opsional',
            'img_bukti' => 'bukti pembayaran',
            'status_biodata' => 'status pendaftaran',
            'status_berkas' => 'status verifikasi',
            'status_pemb' => 'status pembayaran',
        ];
    }

    private function pmbStatusValues(): array
    {
        return array_map(fn (PmbStatus $status) => $status->value, PmbStatus::cases());
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

    private function existingMahasiswa(): ?User
    {
        $userId = $this->route('mahasiswa_baru')
            ?? $this->route('mahasiswa')
            ?? $this->route('id');

        return $userId ? User::find($userId) : null;
    }

    private function hasRequiredDocuments(?User $mahasiswa): bool
    {
        return ($this->hasFile('img_kk') || filled($mahasiswa?->img_kk))
            && ($this->hasFile('img_ktp') || filled($mahasiswa?->img_ktp));
    }
}
