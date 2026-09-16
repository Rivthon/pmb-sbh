<?php

namespace App\Http\Requests\Admin;

use App\Enums\PmbStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickUpdateMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        if ($this->isDocumentOfficer()) {
            return [
                'status_berkas' => ['required', 'integer', 'in:0,1,2'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'periode_id' => ['nullable', 'exists:periodes,id'],
            'gelombang_id' => [
                'nullable',
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
            'status_biodata' => ['nullable', 'integer', 'in:0,1'],
            'status_berkas' => ['nullable', 'integer', 'in:0,1,2'],
            'status_pemb' => ['nullable', 'integer', Rule::in($this->pmbStatusValues())],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $mahasiswa = $this->existingMahasiswa();
            $statusPemb = $this->filled('status_pemb')
                ? (int) $this->input('status_pemb')
                : (int) ($mahasiswa?->status_pemb ?? PmbStatus::BelumUpdateBerkas->value);

            if ($statusPemb < PmbStatus::Verified->value) {
                return;
            }

            $statusBiodata = $this->filled('status_biodata')
                ? (int) $this->input('status_biodata')
                : (int) ($mahasiswa?->status_biodata ?? 0);
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

    public function messages(): array
    {
        return [
            'name.required' => 'Nama mahasiswa wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor HP/WhatsApp harus berisi 8-20 digit/karakter nomor yang valid.',
            'jurusan_id.required' => 'Program studi wajib dipilih.',
            'exists' => 'Pilihan :attribute tidak valid.',
            'in' => 'Pilihan :attribute tidak valid.',
            'status_pemb.in' => 'Status pembayaran/alur PMB tidak valid.',
            'regex' => 'Format :attribute tidak valid.',
        ];
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
            'status_biodata' => 'status pendaftaran',
            'status_berkas' => 'status verifikasi',
            'status_pemb' => 'status pembayaran/alur PMB',
        ];
    }

    private function pmbStatusValues(): array
    {
        return array_map(fn (PmbStatus $status) => $status->value, PmbStatus::cases());
    }

    private function existingMahasiswa(): ?User
    {
        $userId = $this->route('id');

        return $userId ? User::find($userId) : null;
    }

    private function hasRequiredDocuments(?User $mahasiswa): bool
    {
        return filled($mahasiswa?->img_kk)
            && filled($mahasiswa?->img_ktp);
    }

    private function isDocumentOfficer(): bool
    {
        $admin = auth('admin')->user();

        return $admin
            && $admin->can(\App\Support\AdminPermissions::PMB_VERIFY_DOCUMENT)
            && !$admin->can(\App\Support\AdminPermissions::PMB_EDIT);
    }
}
