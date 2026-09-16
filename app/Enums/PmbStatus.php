<?php

namespace App\Enums;

enum PmbStatus: int
{
    case BelumUpdateBerkas = 0;
    case MenungguValidasi = 1;
    case Verified = 2;
    case Lulus = 3;
    case TidakLulus = 4;

    public static function fromValue(mixed $value): self
    {
        return self::tryFrom((int) $value) ?? self::BelumUpdateBerkas;
    }

    public function label(): string
    {
        return match ($this) {
            self::BelumUpdateBerkas => 'Belum Update Berkas',
            self::MenungguValidasi => 'Menunggu Validasi Admin',
            self::Verified => 'Verified',
            self::Lulus => 'Lulus',
            self::TidakLulus => 'Tidak Lulus',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BelumUpdateBerkas => 'warning',
            self::MenungguValidasi => 'info',
            self::Verified => 'success',
            self::Lulus => 'primary',
            self::TidakLulus => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BelumUpdateBerkas => 'bx-folder-minus',
            self::MenungguValidasi => 'bx-time-five',
            self::Verified => 'bx-check-shield',
            self::Lulus => 'bx-user-check',
            self::TidakLulus => 'bx-x-circle',
        };
    }

    public function nextReviewStatus(): self
    {
        return match ($this) {
            self::BelumUpdateBerkas => self::MenungguValidasi,
            self::MenungguValidasi => self::Verified,
            default => self::BelumUpdateBerkas,
        };
    }

    public function reviewActionLabel(): string
    {
        return match ($this) {
            self::BelumUpdateBerkas => 'Setujui PMB',
            self::MenungguValidasi => 'Validasi Berkas',
            default => 'Batalkan PMB',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
            self::cases()
        );
    }
}
