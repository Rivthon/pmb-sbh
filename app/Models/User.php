<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Sale;
use App\Models\Agama;
use App\Models\Jurusan;
use App\Models\Periode;
use App\Models\Provinsi;
use App\Models\Gelombang;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Kuesioner;
use App\Models\PekerjaanIbu;
use App\Models\PekerjaanAyah;
use App\Traits\Models\WithUuid;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PenghasilanOrangTua;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Utilities\WithCodeGenerator;
use App\Support\PmbStorage;
use Illuminate\Support\Facades\Storage;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, WithUuid, WithCodeGenerator;

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'periode_id',
        'gelombang_id',
        'jurusan_id',
        'kuesioner_id',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'jenis_kelamin',
        'code',
        'address',
        'uuid',
        'agama_id',
        'pek_ayah_id',
        'pek_ibu_id',
        'penghasilan_id',
        'nisn',
        'nik',
        'no_telp_ortu',
        'status_pemb',
        'status_biodata',
        'status_berkas',
        'asal_sekolah',
        'nama_ayah',
        'nama_ibu',
        'nama_wali',
        'img_kk',
        'img_ktp',
        'img_bukti',
        'img_ijazah',
        'tempat_lahir',
        'tgl_lahir',
        'email_verified_at',
        'verification_code',
        'verification_code_expires_at',
        'verification_attempts',
        'image',
    ];

    /** @var array<int, string> */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
        'password_plaintext',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
    ];

    public const ADMIN_ROLE = 'admin';
    public const USER_ROLE = 'pengguna';
    public const ADMIN_PREFIX_CODE = 'ADM';
    public const USER_PREFIX_CODE = 'MHS';
    public const FOLDER_NAME = 'user/avatar';

    /**
     * Generate a unique code with a prefix and date.
     */
    public function generateUniqueCode(string $uniquePrefixCode): string
    {
        $baseLength = 12;
        $codeLength = $baseLength + strlen($uniquePrefixCode);

        return IdGenerator::generate([
            'table' => $this->getTable(),
            'field' => 'code',
            'length' => $codeLength,
            'prefix' => sprintf('%s-%s-', $uniquePrefixCode, now()->format('dmy')),
            'reset_on_prefix_change' => true,
        ]);
    }

    /** Scope only verified users */
    public function scopeIsActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /** Scope only PMB verified users */
    public function scopeIsPmbVerified($query)
    {
        return $query->where('status_pemb', \App\Enums\PmbStatus::Verified->value);
    }

    /** Get the full profile image URL */
    public function getProfileImageURL(): string
    {
        return asset('storage/' . self::FOLDER_NAME . '/' . ($this->image ?? 'default.png'));
    }

    public function pmbDocumentUrl(string $attribute): ?string
    {
        $fileName = $this->{$attribute};

        if (!$fileName) {
            return null;
        }

        $folders = match ($attribute) {
            'img_kk' => [PmbStorage::DOKUMEN_KK, 'uploads/kk'],
            'img_ktp' => [PmbStorage::DOKUMEN_KTP, 'uploads/ktp'],
            'img_ijazah' => [PmbStorage::DOKUMEN_IJAZAH, 'uploads/ijazah'],
            'img_bukti' => [PmbStorage::PEMBAYARAN, 'uploads/buktibayar'],
            default => [PmbStorage::DOKUMEN],
        };

        foreach ($folders as $folder) {
            if (Storage::disk('public')->exists($folder . '/' . $fileName)) {
                return asset('storage/' . $folder . '/' . $fileName);
            }
        }

        return asset('storage/' . $folders[0] . '/' . $fileName);
    }

    public static function pmbRequiredDocuments(): array
    {
        return [
            'img_kk' => 'Kartu Keluarga',
            'img_ktp' => 'KTP',
        ];
    }

    public static function pmbOptionalDocuments(): array
    {
        return [
            'img_ijazah' => 'Ijazah / SKL',
        ];
    }

    public function missingPmbRequiredDocuments(): array
    {
        return collect(self::pmbRequiredDocuments())
            ->filter(fn (string $label, string $attribute) => blank($this->{$attribute}))
            ->all();
    }

    public function hasPmbRequiredDocuments(): bool
    {
        return empty($this->missingPmbRequiredDocuments());
    }

    public function missingPmbRequiredDocumentsLabel(string $completeLabel = 'Berkas Lengkap'): string
    {
        $missingDocuments = array_values($this->missingPmbRequiredDocuments());

        if (empty($missingDocuments)) {
            return $completeLabel;
        }

        return 'Belum ' . implode(', ', $missingDocuments);
    }

    // ========================
    // 🔗 Relationships
    // ========================



    public function gelombang()
    {
        return $this->belongsTo(Gelombang::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id_prov');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id_kab');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id_kec');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id', 'id_kel');
    }

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class, 'kuesioner_id', 'id');
    }

    public function agama()
    {
        return $this->belongsTo(Agama::class);
    }

    public function pekerjaanAyah()
    {
        return $this->belongsTo(PekerjaanAyah::class, 'pek_ayah_id');
    }

    public function pekerjaanIbu()
    {
        return $this->belongsTo(PekerjaanIbu::class, 'pek_ibu_id');
    }

    public function penghasilan()
    {
        return $this->belongsTo(PenghasilanOrangTua::class, 'penghasilan_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function periodeAktif()
    {
        return $this->belongsTo(Periode::class, 'periode_id')->where('status_periode', 'aktif');
    }
    public function tesKesehatanAnamnesa()
    {
        return $this->hasOne(TesKesehatanAnamnesa::class);
    }

    public function tesWawancara()
    {
        return $this->hasOne(TesWawancara::class);
    }
    public function wawancaraPmb()
    {
        return $this->hasOne(WawancaraPmb::class, 'calon_mahasiswa_id');
    }

    public function pmbOfflineQueues()
    {
        return $this->hasMany(PmbOfflineQueue::class);
    }
}
