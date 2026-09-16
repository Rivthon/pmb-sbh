<?php

namespace App\Services\Pmb;

use App\Enums\PmbStatus;
use App\Events\MahasiswaRegistered;
use App\Events\PmbStatusUpdated;
use App\Jobs\SendPmbResultEmail;
use App\Models\Agama;
use App\Models\Gelombang;
use App\Models\Jurusan;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\PekerjaanAyah;
use App\Models\PekerjaanIbu;
use App\Models\PenghasilanOrangTua;
use App\Models\Periode;
use App\Models\Provinsi;
use App\Models\User;
use App\Services\Audit\ActivityLogger;
use App\Support\PmbStorage;
use App\Traits\Utilities\HandleUploadedFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MahasiswaBaruService
{
    use HandleUploadedFile;

    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->when(!empty($filters['search']), fn (Builder $query) => $query->where('name', 'like', '%' . $filters['search'] . '%'))
            ->when(!empty($filters['periode']), fn (Builder $query) => $query->where('periode_id', $filters['periode']))
            ->when(!empty($filters['gelombang']), fn (Builder $query) => $query->where('gelombang_id', $filters['gelombang']))
            ->when(isset($filters['status_pemb']) && $filters['status_pemb'] !== '', fn (Builder $query) => $query->where('status_pemb', $filters['status_pemb']))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateGelombangAktif(int $gelombangId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->where('gelombang_id', $gelombangId)
            ->whereHas('periode', fn (Builder $query) => $query->where('status_periode', 'aktif'))
            ->when(!empty($filters['search']), fn (Builder $query) => $query->where('name', 'like', '%' . $filters['search'] . '%'))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        // Handle file uploads
        if (!empty($data['image'])) {
            $data['image'] = $this->uploadFile($data['image'], User::FOLDER_NAME);
        }
        if (!empty($data['img_ktp'])) {
            $data['img_ktp'] = $this->uploadFile($data['img_ktp'], PmbStorage::DOKUMEN_KTP);
        }
        if (!empty($data['img_kk'])) {
            $data['img_kk'] = $this->uploadFile($data['img_kk'], PmbStorage::DOKUMEN_KK);
        }
        if (!empty($data['img_ijazah'])) {
            $data['img_ijazah'] = $this->uploadFile($data['img_ijazah'], PmbStorage::DOKUMEN_IJAZAH);
        }
        if (!empty($data['img_bukti'])) {
            $data['img_bukti'] = $this->uploadFile($data['img_bukti'], PmbStorage::PEMBAYARAN);
        }

        $this->assertPaymentStatusAllowed(null, (int) ($data['status_pemb'] ?? PmbStatus::BelumUpdateBerkas->value), $data);

        $mahasiswa = DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            $data['role'] = User::USER_ROLE;
            $data['code'] = (new User())->generateUniqueCode(User::USER_PREFIX_CODE);
            $data['status_pemb'] = $data['status_pemb'] ?? PmbStatus::BelumUpdateBerkas->value;
            $data['status_biodata'] = $data['status_biodata'] ?? 1;
            $data['status_berkas'] = $data['status_berkas'] ?? 0;

            return User::create($data);
        });

        MahasiswaRegistered::dispatch($mahasiswa);
        $this->activityLogger->log(
            'pmb',
            'mahasiswa.created',
            'Admin membuat data mahasiswa PMB',
            $mahasiswa,
            [],
            $mahasiswa->only(['id', 'name', 'email', 'phone', 'jurusan_id', 'periode_id', 'gelombang_id'])
        );

        return $mahasiswa;
    }

    public function update(User $mahasiswa, array $data): User
    {
        // Handle file uploads
        if (!empty($data['image'])) {
            $data['image'] = $this->syncUploadFile($data['image'], $mahasiswa->image, User::FOLDER_NAME);
        } else {
            unset($data['image']);
        }
        
        if (!empty($data['img_ktp'])) {
            $data['img_ktp'] = $this->syncUploadFile($data['img_ktp'], $mahasiswa->img_ktp, PmbStorage::DOKUMEN_KTP);
        } else {
            unset($data['img_ktp']);
        }
        
        if (!empty($data['img_kk'])) {
            $data['img_kk'] = $this->syncUploadFile($data['img_kk'], $mahasiswa->img_kk, PmbStorage::DOKUMEN_KK);
        } else {
            unset($data['img_kk']);
        }
        
        if (!empty($data['img_ijazah'])) {
            $data['img_ijazah'] = $this->syncUploadFile($data['img_ijazah'], $mahasiswa->img_ijazah, PmbStorage::DOKUMEN_IJAZAH);
        } else {
            unset($data['img_ijazah']);
        }
        
        if (!empty($data['img_bukti'])) {
            $data['img_bukti'] = $this->syncUploadFile($data['img_bukti'], $mahasiswa->img_bukti, PmbStorage::PEMBAYARAN);
        } else {
            unset($data['img_bukti']);
        }

        $this->assertPaymentStatusAllowed(
            $mahasiswa,
            (int) ($data['status_pemb'] ?? $mahasiswa->status_pemb ?? PmbStatus::BelumUpdateBerkas->value),
            $data
        );

        $oldValues = $mahasiswa->only(array_keys($data));

        $updated = DB::transaction(function () use ($mahasiswa, $data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $mahasiswa->update($data);

            return $mahasiswa->refresh();
        });

        $this->activityLogger->log(
            'pmb',
            'mahasiswa.updated',
            'Admin memperbarui data mahasiswa PMB',
            $updated,
            $oldValues,
            $updated->only(array_keys($data))
        );

        return $updated;
    }

    public function delete(User $mahasiswa): void
    {
        $oldValues = $mahasiswa->only(['id', 'name', 'email', 'phone', 'jurusan_id', 'periode_id', 'gelombang_id', 'status_pemb']);

        DB::transaction(fn () => $mahasiswa->delete());

        $this->activityLogger->log(
            'pmb',
            'mahasiswa.deleted',
            'Admin menghapus data mahasiswa PMB',
            $mahasiswa,
            $oldValues
        );
    }

    public function restore(int|string $id): User
    {
        $mahasiswa = User::withTrashed()
            ->where('role', User::USER_ROLE)
            ->findOrFail($id);

        DB::transaction(fn () => $mahasiswa->restore());

        $this->activityLogger->log(
            'pmb',
            'mahasiswa.restored',
            'Admin memulihkan data mahasiswa PMB',
            $mahasiswa
        );

        return $mahasiswa->refresh();
    }

    public function forceDelete(int|string $id): void
    {
        $mahasiswa = User::withTrashed()
            ->where('role', User::USER_ROLE)
            ->findOrFail($id);

        $oldValues = $mahasiswa->only(['id', 'name', 'email', 'phone', 'jurusan_id', 'periode_id', 'gelombang_id', 'status_pemb']);

        DB::transaction(fn () => $mahasiswa->forceDelete());

        $this->activityLogger->log(
            'pmb',
            'mahasiswa.force-deleted',
            'Super Admin menghapus permanen data mahasiswa PMB',
            null,
            $oldValues
        );
    }

    public function advanceReviewStatus(User $mahasiswa): User
    {
        $oldStatus = PmbStatus::fromValue($mahasiswa->status_pemb);
        $newStatus = $oldStatus->nextReviewStatus();

        $this->assertPaymentStatusAllowed($mahasiswa, $newStatus->value);

        $updated = DB::transaction(function () use ($mahasiswa, $newStatus) {
            $mahasiswa->status_pemb = $newStatus->value;
            $mahasiswa->save();

            return $mahasiswa->refresh();
        });

        PmbStatusUpdated::dispatch($updated, $oldStatus, PmbStatus::fromValue($updated->status_pemb));

        return $updated;
    }

    public function findOrFail(int|string $id): User
    {
        return $this->query()->findOrFail($id);
    }

    public function exportQuery(array $filters = []): Builder
    {
        return $this->query()
            ->when(!empty($filters['search']), fn (Builder $query) => $query->where('name', 'like', '%' . $filters['search'] . '%'))
            ->when(!empty($filters['periode']), fn (Builder $query) => $query->where('periode_id', $filters['periode']))
            ->when(!empty($filters['gelombang']), fn (Builder $query) => $query->where('gelombang_id', $filters['gelombang']))
            ->when(isset($filters['status_pemb']) && $filters['status_pemb'] !== '', fn (Builder $query) => $query->where('status_pemb', $filters['status_pemb']));
    }

    public function formOptions(?User $mahasiswa = null): array
    {
        $selectedPeriodeId = $mahasiswa?->periode_id;
        
        if (!$selectedPeriodeId) {
            $activePeriode = Periode::where('status_periode', 'aktif')->first() ?? Periode::first();
            $selectedPeriodeId = $activePeriode?->id;
        }

        $gelombangs = $selectedPeriodeId 
            ? Gelombang::where('periode_id', $selectedPeriodeId)->orderBy('tgl_mulai', 'desc')->get()
            : collect();

        return [
            'periodes' => Periode::orderBy('tgl_mulai', 'desc')->get(),
            'gelombangs' => $gelombangs,
            'jurusan' => Jurusan::orderBy('nama_jurusan')->get(),
            'provinsi' => Provinsi::orderBy('nama')->get(),
            'kabupaten' => $mahasiswa?->provinsi_id
                ? Kabupaten::where('id_prov', $mahasiswa->provinsi_id)->orderBy('nama_kab')->get()
                : collect(),
            'kecamatan' => $mahasiswa?->kabupaten_id
                ? Kecamatan::where('id_kab', $mahasiswa->kabupaten_id)->orderBy('nama_kec')->get()
                : collect(),
            'kelurahan' => $mahasiswa?->kecamatan_id
                ? Kelurahan::where('id_kec', $mahasiswa->kecamatan_id)->orderBy('nama_kel')->get()
                : collect(),
            'agama' => Agama::orderBy('nama_agama')->get(),
            'pekerjaanAyah' => PekerjaanAyah::orderBy('nama_pek_ayah')->get(),
            'pekerjaanIbu' => PekerjaanIbu::orderBy('nama_pek_ibu')->get(),
            'penghasilan' => PenghasilanOrangTua::orderBy('nama_peng')->get(),
            'pmbStatusOptions' => PmbStatus::options(),
        ];
    }

    public function sendKelulusanEmail(User $mahasiswa): void
    {
        $this->assertNotificationReady($mahasiswa);
        $this->assertPaymentStatusAllowed($mahasiswa, PmbStatus::Lulus->value);

        $oldStatus = PmbStatus::fromValue($mahasiswa->status_pemb);

        DB::transaction(function () use ($mahasiswa) {
            $mahasiswa->update(['status_pemb' => PmbStatus::Lulus->value]);
        });

        PmbStatusUpdated::dispatch($mahasiswa->refresh(), $oldStatus, PmbStatus::Lulus);
        SendPmbResultEmail::dispatch($mahasiswa->id, PmbStatus::Lulus)->afterCommit();
    }

    public function sendTidakLulusEmail(User $mahasiswa): void
    {
        $this->assertNotificationReady($mahasiswa);

        $oldStatus = PmbStatus::fromValue($mahasiswa->status_pemb);

        DB::transaction(function () use ($mahasiswa) {
            $mahasiswa->update(['status_pemb' => PmbStatus::TidakLulus->value]);
        });

        PmbStatusUpdated::dispatch($mahasiswa->refresh(), $oldStatus, PmbStatus::TidakLulus);
        SendPmbResultEmail::dispatch($mahasiswa->id, PmbStatus::TidakLulus)->afterCommit();
    }

    public function query(): Builder
    {
        return User::query()
            ->with([
                'jurusan',
                'periode',
                'gelombang',
                'provinsi',
                'kabupaten',
                'kecamatan',
                'kelurahan',
                'agama',
                'pekerjaanAyah',
                'pekerjaanIbu',
                'penghasilan',
            ])
            ->where('role', User::USER_ROLE)
            ->latest();
    }

    private function assertNotificationReady(User $mahasiswa): void
    {
        if (empty($mahasiswa->email) || !filter_var($mahasiswa->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email mahasiswa tidak valid atau belum diisi.');
        }

        if (!Periode::where('status_periode', 'aktif')->exists()) {
            throw new \RuntimeException('Periode aktif tidak ditemukan. Harap periksa data periode.');
        }
    }

    private function assertPaymentStatusAllowed(?User $mahasiswa, int $targetStatus, array $data = []): void
    {
        if ($targetStatus < PmbStatus::Verified->value) {
            return;
        }

        $statusBiodata = (int) ($data['status_biodata'] ?? $mahasiswa?->status_biodata ?? 1);
        $statusBerkas = (int) ($data['status_berkas'] ?? $mahasiswa?->status_berkas ?? 0);

        $hasDocuments = filled($data['img_kk'] ?? $mahasiswa?->img_kk)
            && filled($data['img_ktp'] ?? $mahasiswa?->img_ktp);

        if ($statusBiodata !== 1) {
            throw new \InvalidArgumentException('Status PMB tidak boleh diverifikasi sebelum biodata lengkap.');
        }

        if ($statusBerkas !== 1 || !$hasDocuments) {
            throw new \InvalidArgumentException('Status PMB tidak boleh diverifikasi sebelum berkas wajib lengkap.');
        }
    }
}
