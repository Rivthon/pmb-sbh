<?php

namespace App\Support;

final class PmbStorage
{
    public const BASE = 'pmb';
    public const DOKUMEN = self::BASE . '/dokumen';
    public const DOKUMEN_KK = self::DOKUMEN . '/kk';
    public const DOKUMEN_KTP = self::DOKUMEN . '/ktp';
    public const DOKUMEN_IJAZAH = self::DOKUMEN . '/ijazah';
    public const PEMBAYARAN = self::BASE . '/pembayaran';
    public const FOTO = self::BASE . '/foto';
}
