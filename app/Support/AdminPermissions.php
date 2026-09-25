<?php

namespace App\Support;

final class AdminPermissions
{
    public const GUARD = 'admin';

    public const DASHBOARD_VIEW = 'dashboard.view';

    public const PMB_VIEW = 'pmb.view';
    public const PMB_CREATE = 'pmb.create';
    public const PMB_EDIT = 'pmb.edit';
    public const PMB_DELETE = 'pmb.delete';
    public const PMB_RESTORE = 'pmb.restore';
    public const PMB_FORCE_DELETE = 'pmb.force-delete';
    public const PMB_UPDATE_STATUS = 'pmb.update-status';
    public const PMB_VERIFY_DOCUMENT = 'pmb.verify-document';
    public const PMB_EXPORT = 'pmb.export';
    public const PMB_SEND_EMAIL = 'pmb.send-email';
    public const PMB_IMPERSONATE = 'pmb.impersonate';

    public const PAYMENT_VIEW = 'payment.view';
    public const PAYMENT_VERIFY = 'payment.verify';
    public const PAYMENT_REJECT = 'payment.reject';
    public const PAYMENT_EXPORT = 'payment.export';

    public const MASTER_VIEW = 'master.view';
    public const MASTER_CREATE = 'master.create';
    public const MASTER_EDIT = 'master.edit';
    public const MASTER_DELETE = 'master.delete';
    public const MASTER_RESTORE = 'master.restore';

    public const PERIODE_VIEW = 'periode.view';
    public const PERIODE_CREATE = 'periode.create';
    public const PERIODE_EDIT = 'periode.edit';
    public const PERIODE_DELETE = 'periode.delete';
    public const PERIODE_SET_ACTIVE = 'periode.set-active';

    public const GELOMBANG_VIEW = 'gelombang.view';
    public const GELOMBANG_CREATE = 'gelombang.create';
    public const GELOMBANG_EDIT = 'gelombang.edit';
    public const GELOMBANG_DELETE = 'gelombang.delete';
    public const GELOMBANG_SET_ACTIVE = 'gelombang.set-active';

    public const JURUSAN_VIEW = 'jurusan.view';
    public const JURUSAN_CREATE = 'jurusan.create';
    public const JURUSAN_EDIT = 'jurusan.edit';
    public const JURUSAN_DELETE = 'jurusan.delete';

    public const USER_VIEW = 'user.view';
    public const USER_CREATE = 'user.create';
    public const USER_EDIT = 'user.edit';
    public const USER_DELETE = 'user.delete';
    public const USER_ASSIGN_ROLE = 'user.assign-role';

    public const ROLE_VIEW = 'role.view';
    public const ROLE_CREATE = 'role.create';
    public const ROLE_EDIT = 'role.edit';
    public const ROLE_DELETE = 'role.delete';
    public const ROLE_ASSIGN_PERMISSION = 'role.assign-permission';

    public const ACTIVITY_LOG_VIEW = 'activity-log.view';
    public const ACTIVITY_LOG_EXPORT = 'activity-log.export';
    public const ACTIVITY_LOG_DELETE = 'activity-log.delete';

    public const REPORT_VIEW = 'report.view';
    public const REPORT_EXPORT = 'report.export';

    public const SETTING_VIEW = 'setting.view';
    public const SETTING_EDIT = 'setting.edit';

    public const WAWANCARA_REVIEW = 'wawancara.review';
    public const KESEHATAN_REVIEW = 'kesehatan.review';
    public const PMB_QUEUE_TES_TULIS = 'pmb-queue.tes-tulis';
    public const PMB_QUEUE_KESEHATAN = 'pmb-queue.kesehatan';
    public const PMB_QUEUE_WAWANCARA = 'pmb-queue.wawancara';
    public const PMB_QUEUE_SCAN = 'pmb-queue.scan';
    public const PMB_QUEUE_ONLINE_MANAGE = 'pmb-queue.online.manage';
    public const PMB_ONLINE_TES_TULIS_MANAGE = 'pmb-online.tes-tulis.manage';
    public const PMB_ONLINE_SOAL_MANAGE = 'pmb-online.soal.manage';
    public const PMB_ONLINE_HASIL_TES_VIEW = 'pmb-online.hasil-tes.view';
    public const PMB_ONLINE_KESEHATAN_MANAGE = 'pmb-online.kesehatan.manage';
    public const PMB_ONLINE_WAWANCARA_MANAGE = 'pmb-online.wawancara.manage';
    public const LANDING_PAGE_MANAGE = 'landing-page.manage';

    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_ADMIN_PMB = 'Admin PMB';
    public const ROLE_STAFF_PMB = 'Staff PMB';
    public const ROLE_PETUGAS_PEMBERKASAN = 'Petugas Pemberkasan';
    public const ROLE_PETUGAS_TES_TULIS = 'Petugas Tes Tulis';
    public const ROLE_PJ_SELEKSI_ONLINE = 'PJ Seleksi Online';
    public const ROLE_KEUANGAN = 'Keuangan';
    public const ROLE_BAAK = 'BAAK';
    public const ROLE_UPMI = 'UPMI';
    public const ROLE_BAUK = 'BAUK';
    public const ROLE_KEMAHASISWAAN = 'Kemahasiswaan';
    public const ROLE_DOSEN = 'Dosen';
    public const ROLE_MAHASISWA = 'Mahasiswa';

    public const LEGACY_ROLE_PANITIA_PMB = 'Panitia PMB';
    public const LEGACY_ROLE_ADMIN = 'Admin';
    public const LEGACY_ROLE_DOSEN_PEWAWANCARA = 'Dosen Pewawancara';
    public const LEGACY_ROLE_PETUGAS_MEDIS = 'Petugas Medis';

    public static function modules(): array
    {
        return [
            'Dashboard' => [self::DASHBOARD_VIEW],
            'PMB Mahasiswa' => [
                self::PMB_VIEW,
                self::PMB_CREATE,
                self::PMB_EDIT,
                self::PMB_DELETE,
                self::PMB_RESTORE,
                self::PMB_FORCE_DELETE,
                self::PMB_UPDATE_STATUS,
                self::PMB_VERIFY_DOCUMENT,
                self::PMB_EXPORT,
                self::PMB_SEND_EMAIL,
                self::PMB_IMPERSONATE,
            ],
            'Pembayaran' => [
                self::PAYMENT_VIEW,
                self::PAYMENT_VERIFY,
                self::PAYMENT_REJECT,
                self::PAYMENT_EXPORT,
            ],
            'Data Master' => [
                self::MASTER_VIEW,
                self::MASTER_CREATE,
                self::MASTER_EDIT,
                self::MASTER_DELETE,
                self::MASTER_RESTORE,
            ],
            'Periode' => [
                self::PERIODE_VIEW,
                self::PERIODE_CREATE,
                self::PERIODE_EDIT,
                self::PERIODE_DELETE,
                self::PERIODE_SET_ACTIVE,
            ],
            'Gelombang' => [
                self::GELOMBANG_VIEW,
                self::GELOMBANG_CREATE,
                self::GELOMBANG_EDIT,
                self::GELOMBANG_DELETE,
                self::GELOMBANG_SET_ACTIVE,
            ],
            'Program Studi / Jurusan' => [
                self::JURUSAN_VIEW,
                self::JURUSAN_CREATE,
                self::JURUSAN_EDIT,
                self::JURUSAN_DELETE,
            ],
            'User Management' => [
                self::USER_VIEW,
                self::USER_CREATE,
                self::USER_EDIT,
                self::USER_DELETE,
                self::USER_ASSIGN_ROLE,
            ],
            'Role Permission' => [
                self::ROLE_VIEW,
                self::ROLE_CREATE,
                self::ROLE_EDIT,
                self::ROLE_DELETE,
                self::ROLE_ASSIGN_PERMISSION,
            ],
            'Activity Log' => [
                self::ACTIVITY_LOG_VIEW,
                self::ACTIVITY_LOG_EXPORT,
                self::ACTIVITY_LOG_DELETE,
            ],
            'Export / Report' => [
                self::REPORT_VIEW,
                self::REPORT_EXPORT,
            ],
            'Setting' => [
                self::SETTING_VIEW,
                self::SETTING_EDIT,
            ],
            'Seleksi' => [
                self::WAWANCARA_REVIEW,
                self::KESEHATAN_REVIEW,
                self::PMB_QUEUE_TES_TULIS,
                self::PMB_QUEUE_KESEHATAN,
                self::PMB_QUEUE_WAWANCARA,
                self::PMB_QUEUE_SCAN,
                self::PMB_QUEUE_ONLINE_MANAGE,
                self::PMB_ONLINE_TES_TULIS_MANAGE,
                self::PMB_ONLINE_SOAL_MANAGE,
                self::PMB_ONLINE_HASIL_TES_VIEW,
                self::PMB_ONLINE_KESEHATAN_MANAGE,
                self::PMB_ONLINE_WAWANCARA_MANAGE,
            ],
            'Landing Page' => [
                self::LANDING_PAGE_MANAGE,
            ],
        ];
    }

    public static function all(): array
    {
        return array_values(array_unique(array_merge(...array_values(self::modules()))));
    }

    public static function roles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN_PMB,
            self::ROLE_STAFF_PMB,
            self::ROLE_PETUGAS_PEMBERKASAN,
            self::ROLE_PETUGAS_TES_TULIS,
            self::ROLE_PJ_SELEKSI_ONLINE,
            self::ROLE_KEUANGAN,
            self::ROLE_BAAK,
            self::ROLE_UPMI,
            self::ROLE_BAUK,
            self::ROLE_KEMAHASISWAAN,
            self::ROLE_DOSEN,
            self::ROLE_MAHASISWA,
        ];
    }

    public static function roleMap(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => self::all(),
            self::ROLE_ADMIN_PMB => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::PMB_CREATE,
                self::PMB_EDIT,
                self::PMB_DELETE,
                self::PMB_RESTORE,
                self::PMB_UPDATE_STATUS,
                self::PMB_VERIFY_DOCUMENT,
                self::PMB_EXPORT,
                self::PMB_SEND_EMAIL,
                self::PMB_IMPERSONATE,
                self::PMB_QUEUE_TES_TULIS,
                self::PMB_QUEUE_KESEHATAN,
                self::PMB_QUEUE_WAWANCARA,
                self::PAYMENT_VIEW,
                self::PAYMENT_VERIFY,
                self::PAYMENT_REJECT,
                self::PAYMENT_EXPORT,
                self::MASTER_VIEW,
                self::MASTER_CREATE,
                self::MASTER_EDIT,
                self::MASTER_DELETE,
                self::PERIODE_VIEW,
                self::PERIODE_CREATE,
                self::PERIODE_EDIT,
                self::PERIODE_DELETE,
                self::PERIODE_SET_ACTIVE,
                self::GELOMBANG_VIEW,
                self::GELOMBANG_CREATE,
                self::GELOMBANG_EDIT,
                self::GELOMBANG_DELETE,
                self::GELOMBANG_SET_ACTIVE,
                self::JURUSAN_VIEW,
                self::JURUSAN_CREATE,
                self::JURUSAN_EDIT,
                self::JURUSAN_DELETE,
                self::REPORT_VIEW,
                self::REPORT_EXPORT,
                self::LANDING_PAGE_MANAGE,
            ],
            self::ROLE_STAFF_PMB => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::PMB_CREATE,
                self::PMB_EDIT,
                self::PMB_UPDATE_STATUS,
                self::PMB_VERIFY_DOCUMENT,
                self::PMB_SEND_EMAIL,
                self::PMB_QUEUE_TES_TULIS,
                self::PMB_QUEUE_KESEHATAN,
                self::PMB_QUEUE_WAWANCARA,
                self::PAYMENT_VIEW,
                self::MASTER_VIEW,
                self::PERIODE_VIEW,
                self::GELOMBANG_VIEW,
                self::JURUSAN_VIEW,
                self::REPORT_VIEW,
            ],
            self::ROLE_PETUGAS_PEMBERKASAN => [
                self::PMB_QUEUE_SCAN,
            ],
            self::ROLE_PETUGAS_TES_TULIS => [
                self::PMB_QUEUE_TES_TULIS,
            ],
            self::ROLE_PJ_SELEKSI_ONLINE => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::PMB_QUEUE_ONLINE_MANAGE,
                self::PMB_ONLINE_TES_TULIS_MANAGE,
                self::PMB_ONLINE_SOAL_MANAGE,
                self::PMB_ONLINE_HASIL_TES_VIEW,
                self::PMB_ONLINE_KESEHATAN_MANAGE,
                self::PMB_ONLINE_WAWANCARA_MANAGE,
            ],
            self::ROLE_KEUANGAN => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::PAYMENT_VIEW,
                self::PAYMENT_VERIFY,
                self::PAYMENT_REJECT,
                self::PAYMENT_EXPORT,
                self::REPORT_VIEW,
                self::REPORT_EXPORT,
            ],
            self::ROLE_BAAK => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::PMB_EXPORT,
                self::MASTER_VIEW,
                self::MASTER_CREATE,
                self::MASTER_EDIT,
                self::PERIODE_VIEW,
                self::GELOMBANG_VIEW,
                self::JURUSAN_VIEW,
                self::REPORT_VIEW,
                self::REPORT_EXPORT,
            ],
            self::ROLE_UPMI => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::REPORT_VIEW,
                self::REPORT_EXPORT,
                self::ACTIVITY_LOG_VIEW,
            ],
            self::ROLE_BAUK => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::PAYMENT_VIEW,
                self::PAYMENT_EXPORT,
                self::REPORT_VIEW,
            ],
            self::ROLE_KEMAHASISWAAN => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::REPORT_VIEW,
            ],
            self::ROLE_DOSEN => [
                self::WAWANCARA_REVIEW,
                self::PMB_QUEUE_WAWANCARA,
            ],
            self::ROLE_MAHASISWA => [],
        ];
    }

    public static function legacyRoleMap(): array
    {
        return [
            self::LEGACY_ROLE_ADMIN => self::roleMap()[self::ROLE_ADMIN_PMB],
            self::LEGACY_ROLE_PANITIA_PMB => self::roleMap()[self::ROLE_ADMIN_PMB],
            self::LEGACY_ROLE_DOSEN_PEWAWANCARA => self::roleMap()[self::ROLE_DOSEN],
            self::LEGACY_ROLE_PETUGAS_MEDIS => [
                self::DASHBOARD_VIEW,
                self::PMB_VIEW,
                self::KESEHATAN_REVIEW,
                self::PMB_QUEUE_KESEHATAN,
            ],
        ];
    }

    public static function legacyPermissionMap(): array
    {
        return [
            'view dashboard' => self::DASHBOARD_VIEW,
            'view mahasiswa baru' => self::PMB_VIEW,
            'create mahasiswa baru' => self::PMB_CREATE,
            'edit mahasiswa baru' => self::PMB_EDIT,
            'delete mahasiswa baru' => self::PMB_DELETE,
            'verifikasi pembayaran' => self::PAYMENT_VERIFY,
            'view laporan' => self::REPORT_VIEW,
            'export laporan' => self::REPORT_EXPORT,
            'edit landing page' => self::LANDING_PAGE_MANAGE,
            'input hasil wawancara' => self::WAWANCARA_REVIEW,
            'view kuesioner' => self::MASTER_VIEW,
            'manage kuesioner' => self::MASTER_EDIT,
        ];
    }
}
