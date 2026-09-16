<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Response;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserTesTulisController;
use App\Http\Controllers\HasilTesTulisController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\LandingPage\MainController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\PmbQueueController;
use App\Http\Controllers\Master\Data\AgamaController;
use App\Http\Controllers\WawancaraMahasiswaController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Master\Data\JurusanController;
use App\Http\Controllers\Master\Data\PeriodeController;
use App\Http\Controllers\Master\Data\SoalTesController;
use App\Http\Controllers\Master\Data\TesTulisController;
use App\Http\Controllers\TesKesehatanAnamnesaController;
use App\Http\Controllers\Master\Data\GelombangController;

use App\Http\Controllers\Master\Data\KuesionerController;
use App\Http\Controllers\Master\Data\PekerjaanIbuController;
use App\Http\Controllers\AdminTesKesehatanAnamnesaController;
use App\Http\Controllers\Master\Data\MahasiswaBaruController;
use App\Http\Controllers\Master\Data\PekerjaanAyahController;
use App\Http\Controllers\Master\Data\PilihanJawabanController;
use App\Http\Controllers\Master\Data\WawancaraReviewController;
use App\Http\Controllers\AdminTesKesehatanPemeriksaanController;
use App\Http\Controllers\Dashboard\MasterData\User\UserController;
use App\Http\Controllers\Dashboard\MasterData\User\AdminController;
use App\Http\Controllers\Master\Data\PenghasilanOrangTuaController;
use App\Http\Controllers\Dashboard\Transaction\IncomingGoodsController;
use App\Http\Controllers\Dashboard\Report\ReportIncomingGoodsController;
use App\Http\Controllers\Dashboard\MasterData\Item\ItemCategoryController;
use App\Http\Controllers\Admin\BiayaKuliahController;
use App\Http\Controllers\Admin\LandingVideoController;
use App\Http\Controllers\Admin\HeroFeatureController;
use App\Http\Controllers\Admin\LandingInfoCardController;
use App\Http\Controllers\Admin\LandingMediaController;
use App\Http\Controllers\Admin\PmbOfflineQueueController;
use App\Support\AdminPermissions;

Route::get('/storage/{filename}', function ($filename) {
    $safeFileName = basename($filename);
    $path = storage_path('app/public/user/avatar/' . $safeFileName);

    if (!file_exists($path)) {
        abort(404);
    }

    return Response::file($path);
})->where('filename', '[A-Za-z0-9._-]+');

Route::get('/landing-media/{filename}', function (string $filename) {
    $safeFileName = basename($filename);
    $path = Storage::disk('local')->path('landing-media/' . $safeFileName);

    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'X-Content-Type-Options' => 'nosniff',
    ]);
})->where('filename', '[A-Za-z0-9._-]+')->name('landing-media.file');


Route::get('/', [MainController::class, 'home'])->name('home');
// Static Pages
Route::view('/pendidikan', 'menu.pendidikan')->name('pendidikan');
Route::view('/tentang-sbh', 'menu.tentang')->name('tentang');
Route::view('/sejarah', 'menu.sejarah')->name('sejarah');
Route::view('/sambutan-ketua-stikes', 'menu.sambutan')->name('sambutan');
Route::post('/hubungi-kami', [MainController::class, 'sendCustomerMessage'])->name('contact.store');

// Berita Controller Routes
Route::get('/berita', [BeritaController::class, 'index'])->name('berita.index');
Route::get('/artikel/stikes-bogor-husada', [BeritaController::class, 'getBerita']);
Route::get('/artikel/{slug}', [BeritaController::class, 'getDetailBerita'])->name('artikel.detail');

// Routes khusus untuk admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Form login admin
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');

    // Proses login admin
    Route::post('login', [AdminAuthController::class, 'storeLogin'])->name('login.store');

    // // Dashboard admin dengan middleware auth:admin
    // Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard')->middleware('auth:admin');

    // Logout admin
    Route::get('/logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth:admin');


    // Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth:admin');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:admin'])
    ->group(function () {
        /** ------------------------------
         * 🧭 Dashboard
         * ----------------------------- */
        Route::controller(AdminDashboardController::class)

            ->group(function () {
                Route::get('dashboard', 'index')->middleware('permission:' . AdminPermissions::DASHBOARD_VIEW . '|' . AdminPermissions::PMB_QUEUE_TES_TULIS . '|' . AdminPermissions::PMB_QUEUE_WAWANCARA . '|' . AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)->name('dashboard');
                Route::get('dashboard/registrasi-data', 'getRegistrasiData')->middleware('permission:' . AdminPermissions::DASHBOARD_VIEW)->name('dashboard.registrasi-data');
                Route::get('dashboard/prodi-chart', 'getProdiChart')->middleware('permission:' . AdminPermissions::DASHBOARD_VIEW)->name('dashboard.prodi-chart');
                Route::get('api/charts/kuesioner', 'getKuesionerChartData')->middleware('permission:' . AdminPermissions::DASHBOARD_VIEW)->name('charts.kuesioner');
            });



        Route::get('mahasiswa-baru', [MahasiswaBaruController::class, 'index'])->middleware('permission:' . AdminPermissions::PMB_VIEW)->name('mahasiswa-baru.index');
        Route::get('mahasiswa-baru/gelombang2', [MahasiswaBaruController::class, 'gelombang2'])->middleware('permission:' . AdminPermissions::PMB_VIEW)->name('mahasiswa-baru.gelombang2');
        Route::get('mahasiswa-baru/gelombang3', [MahasiswaBaruController::class, 'gelombang3'])->middleware('permission:' . AdminPermissions::PMB_VIEW)->name('mahasiswa-baru.gelombang3');
        Route::get('/mahasiswa-baru/filter', [MahasiswaBaruController::class, 'filter'])->middleware('permission:' . AdminPermissions::PMB_VIEW)->name('mahasiswa-baru.filter');
        Route::get('/mahasiswa/cetak', [MahasiswaBaruController::class, 'cetak'])->middleware('permission:' . AdminPermissions::PMB_EXPORT)->name('mahasiswa.cetak');
        Route::get('/mahasiswa/cetak-excel', [MahasiswaBaruController::class, 'cetakExcel'])->middleware('permission:' . AdminPermissions::PMB_EXPORT)->name('mahasiswa.cetak-excel');
        Route::post('/kuesioner/chart-data', [KuesionerController::class, 'chartData'])->middleware('permission:' . AdminPermissions::DASHBOARD_VIEW)->name('kuesioner.chart.data');

        Route::get('mahasiswa-baru/create', [MahasiswaBaruController::class, 'create'])->middleware('permission:' . AdminPermissions::PMB_CREATE)->name('mahasiswa-baru.create');
        Route::post('mahasiswa-baru', [MahasiswaBaruController::class, 'store'])->middleware('permission:' . AdminPermissions::PMB_CREATE)->name('mahasiswa-baru.store');
        Route::get('mahasiswa-baru/{id}/quick-edit', [MahasiswaBaruController::class, 'quickEdit'])->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('mahasiswa-baru.quick-edit');
        Route::put('mahasiswa-baru/{id}/quick-update', [MahasiswaBaruController::class, 'quickUpdate'])->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_VERIFY_DOCUMENT)->name('mahasiswa-baru.quick-update');
        Route::get('mahasiswa-baru/{id}/edit', [MahasiswaBaruController::class, 'edit'])->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('mahasiswa-baru.edit');
        Route::get('mahasiswa-baru/{id}', [MahasiswaBaruController::class, 'detail'])->middleware('permission:' . AdminPermissions::PMB_VIEW)->name('mahasiswa-baru.show');
        Route::put('mahasiswa-baru/{id}', [MahasiswaBaruController::class, 'update'])->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('mahasiswa-baru.update');
        Route::post('mahasiswa-baru/generate-password-bulk', [MahasiswaBaruController::class, 'generatePasswordsBulk'])->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('mahasiswa-baru.generate-password-bulk');
        Route::post('mahasiswa-baru/{id}/generate-password', [MahasiswaBaruController::class, 'generatePassword'])->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('mahasiswa-baru.generate-password');
        Route::delete('mahasiswa-baru/{id}', [MahasiswaBaruController::class, 'destroy'])->middleware('permission:' . AdminPermissions::PMB_DELETE)->name('mahasiswa-baru.destroy');

        Route::put('mahasiswa-baru/{id}/update-status', [MahasiswaBaruController::class, 'updateStatus'])->middleware('permission:' . AdminPermissions::PMB_UPDATE_STATUS)->name('mahasiswa-baru.update-status');
        Route::put('mahasiswa-baru/{id}/restore', [MahasiswaBaruController::class, 'restore'])->middleware('permission:' . AdminPermissions::PMB_RESTORE)->name('mahasiswa-baru.restore');
        Route::delete('mahasiswa-baru/{id}/force-delete', [MahasiswaBaruController::class, 'forceDelete'])
            ->middleware('permission:' . AdminPermissions::PMB_FORCE_DELETE)
            ->name('mahasiswa-baru.force-delete');
        Route::post('/mahasiswa-baru/{id}/send-message', [MahasiswaBaruController::class, 'sendEmailLulus'])->middleware('permission:' . AdminPermissions::PMB_SEND_EMAIL)->name('mahasiswa-baru.sendMessage');
        Route::post('/mahasiswa-baru/{id}/send-failure-message', [MahasiswaBaruController::class, 'sendEmailTidakLulus'])
            ->middleware('permission:' . AdminPermissions::PMB_SEND_EMAIL)
            ->name('mahasiswa-baru.sendFailureMessage');

        Route::get('mahasiswa-baru/detail/{id}', [MahasiswaBaruController::class, 'detail'])->middleware('permission:' . AdminPermissions::PMB_VIEW)->name('mahasiswa-baru.detail');

        Route::prefix('antrian-tes-offline')
            ->name('pmb-queues.')
            ->controller(PmbOfflineQueueController::class)
            ->group(function () {
                $queueOperatePermissions = implode('|', [
                    AdminPermissions::PMB_EDIT,
                    AdminPermissions::PMB_QUEUE_TES_TULIS,
                    AdminPermissions::PMB_QUEUE_KESEHATAN,
                    AdminPermissions::PMB_QUEUE_WAWANCARA,
                ]);
                $queueViewPermissions = AdminPermissions::PMB_VIEW . '|' . $queueOperatePermissions;
                $queueAdminViewPermissions = AdminPermissions::PMB_VIEW . '|' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_ONLINE_MANAGE;

                Route::get('/', 'index')->middleware('permission:' . $queueAdminViewPermissions)->name('index');
                Route::get('/scan', 'scanSessions')->middleware('permission:' . AdminPermissions::PMB_QUEUE_SCAN)->name('scan.index');
                Route::get('/wawancara/sesi', 'interviewSessions')->middleware('permission:' . AdminPermissions::PMB_QUEUE_WAWANCARA)->name('interview.sessions');
                Route::get('/wawancara/offline/sesi', 'interviewOfflineSessions')->middleware('permission:' . AdminPermissions::PMB_QUEUE_WAWANCARA)->name('interview.offline.sessions');
                Route::get('/wawancara/online/sesi', 'interviewOnlineSessions')->middleware('permission:' . AdminPermissions::PMB_QUEUE_WAWANCARA)->name('interview.online.sessions');
                Route::get('/tes-tulis/sesi', 'writtenTestSessions')->middleware('permission:' . AdminPermissions::PMB_QUEUE_TES_TULIS)->name('written-test.sessions');
                Route::get('/{session}/scan', 'scan')->middleware('permission:' . AdminPermissions::PMB_QUEUE_SCAN)->name('scan');
                Route::get('/create', 'create')->middleware('permission:' . AdminPermissions::PMB_CREATE)->name('create');
                Route::post('/', 'store')->middleware('permission:' . AdminPermissions::PMB_CREATE)->name('store');
                Route::get('/check-in/{queue}', 'checkInLink')->middleware(['signed', 'permission:' . AdminPermissions::PMB_EDIT])->name('check-in-link');
                Route::get('/participants/add', 'participantPicker')->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('participants.add');
                Route::get('/queues/{queue}/pass', 'pass')->middleware('permission:' . $queueViewPermissions)->name('pass');
                Route::patch('/queues/{queue}/status', 'updateQueueStatus')->middleware('permission:' . $queueOperatePermissions)->name('queue-status');
                Route::patch('/queues/{queue}/status-json', 'updateQueueStatusJson')->middleware('permission:' . $queueOperatePermissions)->name('queue-status-json');
                Route::delete('/participants/{queue}', 'removeParticipant')->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('participants.destroy');
                Route::put('/rooms/{room}/status', 'updateRoomStatus')->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('rooms.status');
                Route::put('/rooms/{room}', 'updateRoom')->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('rooms.update');
                Route::delete('/rooms/{room}', 'destroyRoom')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_DELETE)->name('rooms.destroy');
                Route::get('/{session}/board', 'board')->middleware('permission:' . $queueViewPermissions)->name('board');
                Route::get('/{session}/stats', 'stats')->middleware('permission:' . $queueAdminViewPermissions)->name('stats');
                Route::get('/{session}/export', 'export')->middleware('permission:' . AdminPermissions::PMB_EXPORT)->name('export');
                Route::post('/{session}/rooms', 'storeRoom')->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('rooms.store');
                Route::post('/{session}/participants', 'assignParticipants')->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('participants.store');
                Route::post('/{session}/check-in', 'manualCheckIn')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_SCAN)->name('check-in');
                Route::post('/{session}/bulk-check-in', 'bulkCheckIn')->middleware('permission:' . AdminPermissions::PMB_EDIT)->name('bulk-check-in');
                Route::post('/{session}/call', 'callNext')->middleware('permission:' . $queueOperatePermissions)->name('call');
                Route::post('/{session}/bulk-status', 'bulkStatus')->middleware('permission:' . $queueOperatePermissions)->name('bulk-status');
                Route::post('/{session}/online/health-notified', 'markOnlineHealthNotified')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)->name('online.health-notified');
                Route::post('/queues/{queue}/online/interview-assignment', 'assignOnlineInterview')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)->name('online.interview-assignment');
                Route::get('/{session}/petugas/wawancara-online', 'officerOnlineWawancara')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_WAWANCARA . '|' . AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)->name('officer.wawancara-online');
                Route::post('/queues/{queue}/online/interview-start', 'startOnlineInterview')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_WAWANCARA)->name('online.interview-start');
                Route::post('/queues/{queue}/online/interview-reentry', 'allowOnlineInterviewReentry')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_WAWANCARA . '|' . AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)->name('online.interview-reentry');
                Route::get('/{session}/petugas/tes-tulis', 'officerTesTulis')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_TES_TULIS)->name('officer.tes-tulis');
                Route::get('/{session}/petugas/kesehatan', 'officerKesehatan')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_KESEHATAN)->name('officer.kesehatan');
                Route::post('/{session}/petugas/kesehatan/ready', 'officerKesehatanReady')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_KESEHATAN)->name('officer.kesehatan.ready');
                Route::get('/{session}/petugas/wawancara', 'officerWawancara')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_WAWANCARA)->name('officer.wawancara');
                Route::post('/{session}/petugas/wawancara/ready', 'officerWawancaraReady')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_WAWANCARA)->name('officer.wawancara.ready');
                Route::get('/{session}/edit', 'edit')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)->name('edit');
                Route::put('/{session}', 'update')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)->name('update');
                Route::delete('/{session}', 'destroy')->middleware('permission:' . AdminPermissions::PMB_EDIT . '|' . AdminPermissions::PMB_DELETE)->name('destroy');
                Route::get('/{session}', 'show')->middleware('permission:' . $queueAdminViewPermissions)->name('show');
            });

        /** ------------------------------
         * 🌍 Lokasi (Kabupaten, Kecamatan, Kelurahan)
         * ----------------------------- */
        Route::get('get-kabupaten/{provinsiId}', [MahasiswaBaruController::class, 'getKabupaten'])->middleware('permission:' . AdminPermissions::PMB_CREATE . '|' . AdminPermissions::PMB_EDIT)->name('get.kabupaten');
        Route::get('get-kecamatan/{kabupatenId}', [MahasiswaBaruController::class, 'getKecamatan'])->middleware('permission:' . AdminPermissions::PMB_CREATE . '|' . AdminPermissions::PMB_EDIT)->name('get.kecamatan');
        Route::get('get-kelurahan/{kecamatanId}', [MahasiswaBaruController::class, 'getKelurahan'])->middleware('permission:' . AdminPermissions::PMB_CREATE . '|' . AdminPermissions::PMB_EDIT)->name('get.kelurahan');

        /** ------------------------------
         * 📝 Kuesioner
         * ----------------------------- */
        Route::resource('kuesioner', KuesionerController::class)
            ->except(['edit'])
            ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::MASTER_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::MASTER_CREATE)
            ->middlewareFor(['update'], 'permission:' . AdminPermissions::MASTER_EDIT)
            ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::MASTER_DELETE);

        Route::post('kuesioner/chart-data', [KuesionerController::class, 'chartData'])
            ->middleware('permission:' . AdminPermissions::DASHBOARD_VIEW)
            ->name('kuesioner.chart.data');

        /** ------------------------------
         * 📚 Master Data
         * ----------------------------- */

        Route::resource('jurusan', JurusanController::class)
            ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::JURUSAN_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::JURUSAN_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::JURUSAN_EDIT)
            ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::JURUSAN_DELETE);
        Route::resource('periode', PeriodeController::class)
            ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::PERIODE_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::PERIODE_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::PERIODE_EDIT)
            ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::PERIODE_DELETE);
        Route::resource('gelombang', GelombangController::class)
            ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::GELOMBANG_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::GELOMBANG_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::GELOMBANG_EDIT)
            ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::GELOMBANG_DELETE);
        Route::resource('agama', AgamaController::class)
            ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::MASTER_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::MASTER_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::MASTER_EDIT)
            ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::MASTER_DELETE);
        Route::resource('pekerjaan-ayah', PekerjaanAyahController::class)
            ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::MASTER_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::MASTER_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::MASTER_EDIT)
            ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::MASTER_DELETE);
        Route::resource('pekerjaan-ibu', PekerjaanIbuController::class)
            ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::MASTER_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::MASTER_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::MASTER_EDIT)
            ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::MASTER_DELETE);
        Route::resource('penghasilan-orang-tua', PenghasilanOrangTuaController::class)
            ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::MASTER_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::MASTER_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::MASTER_EDIT)
            ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::MASTER_DELETE);

        Route::put('periode-pmb/{periode}/aktifkan', [PeriodeController::class, 'aktifkan'])->middleware('permission:' . AdminPermissions::PERIODE_SET_ACTIVE)->name('periode.aktifkan');
        Route::put('periode-pmb/{periode}/nonaktifkan', [PeriodeController::class, 'nonaktifkan'])->middleware('permission:' . AdminPermissions::PERIODE_SET_ACTIVE)->name('periode.nonaktifkan');
        Route::put('gelombang/{gelombang}/aktifkan', [GelombangController::class, 'aktifkan'])->middleware('permission:' . AdminPermissions::GELOMBANG_SET_ACTIVE)->name('gelombang.aktifkan');
        Route::put('gelombang/{gelombang}/nonaktifkan', [GelombangController::class, 'nonaktifkan'])->middleware('permission:' . AdminPermissions::GELOMBANG_SET_ACTIVE)->name('gelombang.nonaktifkan');
        Route::get('gelombang/by-periode/{periode}', [GelombangController::class, 'getGelombangByPeriode'])->name('gelombang.by-periode');


        /** ------------------------------
         * 🧠 Tes Tulis & Soal
         * ----------------------------- */
        Route::resource('tes-tulis', TesTulisController::class)
            ->parameters([
                'tes-tulis' => 'tesTulis'
            ])
            ->middleware('permission:' . AdminPermissions::MASTER_VIEW . '|' . AdminPermissions::MASTER_CREATE . '|' . AdminPermissions::MASTER_EDIT . '|' . AdminPermissions::MASTER_DELETE . '|' . AdminPermissions::PMB_ONLINE_TES_TULIS_MANAGE);


        Route::controller(SoalTesController::class)
            ->prefix('tes-tulis/{tesTulis}/soal')
            ->name('soal.')

            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{soalTes}/edit', 'edit')->name('edit');
                Route::put('/{soalTes}', 'update')->name('update');
                Route::delete('/{soalTes}', 'destroy')->name('destroy');
            })
            ->middleware('permission:' . AdminPermissions::MASTER_VIEW . '|' . AdminPermissions::MASTER_CREATE . '|' . AdminPermissions::MASTER_EDIT . '|' . AdminPermissions::MASTER_DELETE . '|' . AdminPermissions::PMB_ONLINE_SOAL_MANAGE);

        Route::post('soal/{soalTes}/pilihan', [PilihanJawabanController::class, 'store'])
            ->middleware('permission:' . AdminPermissions::MASTER_CREATE . '|' . AdminPermissions::PMB_ONLINE_SOAL_MANAGE)

            ->name('pilihan.store');

        Route::delete('pilihan/{pilihanJawaban}', [PilihanJawabanController::class, 'destroy'])
            ->middleware('permission:' . AdminPermissions::MASTER_DELETE . '|' . AdminPermissions::PMB_ONLINE_SOAL_MANAGE)

            ->name('pilihan.destroy');

        /** ------------------------------
         * 📊 Hasil Tes Tulis
         * ----------------------------- */
        Route::controller(HasilTesTulisController::class)
            ->prefix('hasil-tes')
            ->name('hasil-tes.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'show')->name('show');
                Route::get('/{id}/cetak', 'cetakPdf')->name('cetak');
            })
            ->middleware('permission:' . AdminPermissions::REPORT_VIEW . '|' . AdminPermissions::REPORT_EXPORT . '|' . AdminPermissions::PMB_ONLINE_HASIL_TES_VIEW);


        /** ------------------------------
         * 🌐 Landing Page Management
         * ----------------------------- */
        Route::middleware(['permission:' . AdminPermissions::LANDING_PAGE_MANAGE])->prefix('biaya-kuliah')->name('biaya-kuliah.')->group(function () {
            Route::get('/', [BiayaKuliahController::class, 'index'])->name('index');
            Route::get('/{prodiKey}/edit', [BiayaKuliahController::class, 'edit'])->name('edit');
            Route::put('/{prodiKey}', [BiayaKuliahController::class, 'update'])->name('update');
        });

        Route::middleware(['permission:' . AdminPermissions::LANDING_PAGE_MANAGE])->prefix('landing-videos')->name('landing-videos.')->group(function () {
            Route::get('/', [LandingVideoController::class, 'index'])->name('index');
            Route::get('/create', [LandingVideoController::class, 'create'])->name('create');
            Route::post('/', [LandingVideoController::class, 'store'])->name('store');
            Route::get('/{landingVideo}/edit', [LandingVideoController::class, 'edit'])->name('edit');
            Route::put('/{landingVideo}', [LandingVideoController::class, 'update'])->name('update');
            Route::delete('/{landingVideo}', [LandingVideoController::class, 'destroy'])->name('destroy');
            Route::put('/{landingVideo}/toggle', [LandingVideoController::class, 'toggleActive'])->name('toggle');
        });

        Route::middleware(['permission:' . AdminPermissions::LANDING_PAGE_MANAGE])->prefix('hero-features')->name('hero-features.')->group(function () {
            Route::get('/', [HeroFeatureController::class, 'index'])->name('index');
            Route::put('/{heroFeature}', [HeroFeatureController::class, 'update'])->name('update');
        });

        Route::middleware(['permission:' . AdminPermissions::LANDING_PAGE_MANAGE])->prefix('landing-info-cards')->name('landing-info-cards.')->group(function () {
            Route::get('/', [LandingInfoCardController::class, 'index'])->name('index');
            Route::put('/{landingInfoCard}', [LandingInfoCardController::class, 'update'])->name('update');
        });

        Route::middleware(['permission:' . AdminPermissions::LANDING_PAGE_MANAGE])->prefix('landing-media')->name('landing-media.')->group(function () {
            Route::get('/', [LandingMediaController::class, 'index'])->name('index');
            Route::put('/{landingMedium}', [LandingMediaController::class, 'update'])->name('update');
        });

        /** ------------------------------
         * ⚙️ Manajemen User, Role & Permission
         * ----------------------------- */
        Route::middleware(['permission:' . AdminPermissions::USER_VIEW . '|' . AdminPermissions::ROLE_VIEW])->group(function () {
            Route::put('users/{user}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])
                ->name('users.toggle-active')
                ->middleware('permission:' . AdminPermissions::USER_EDIT);
            Route::put('users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])
                ->name('users.reset-password')
                ->middleware('permission:' . AdminPermissions::USER_EDIT);

            Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
                ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::USER_VIEW)
                ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::USER_CREATE)
                ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::USER_EDIT . '|' . AdminPermissions::USER_ASSIGN_ROLE)
                ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::USER_DELETE);
            Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)
                ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::ROLE_VIEW)
                ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::ROLE_CREATE)
                ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::ROLE_EDIT . '|' . AdminPermissions::ROLE_ASSIGN_PERMISSION)
                ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::ROLE_DELETE);
            Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)
                ->middlewareFor(['index', 'show'], 'permission:' . AdminPermissions::ROLE_VIEW)
                ->middlewareFor(['create', 'store'], 'permission:' . AdminPermissions::ROLE_CREATE)
                ->middlewareFor(['edit', 'update'], 'permission:' . AdminPermissions::ROLE_EDIT . '|' . AdminPermissions::ROLE_ASSIGN_PERMISSION)
                ->middlewareFor(['destroy'], 'permission:' . AdminPermissions::ROLE_DELETE);
        });

        Route::controller(AdminTesKesehatanAnamnesaController::class)
            ->prefix('tes-kesehatan')
            ->name('tes-kesehatan.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/cetak-semua', 'cetakSemua')->name('cetak-semua');
                Route::get('/{id}', 'show')->name('show');
                Route::post('/{id}/anamnesa-autosave', 'autosaveAnamnesa')->name('anamnesa-autosave');
                Route::post('/{id}/pemeriksaan', 'storePemeriksaan')->name('store-pemeriksaan');
                Route::put('/{id}/status', 'updateStatus')->name('update-status');
                Route::delete('/{id}', 'destroy')->name('destroy');
                Route::get('/{id}/cetak', 'cetakPdf')->name('cetak');
            })
            ->middleware('permission:' . AdminPermissions::KESEHATAN_REVIEW . '|' . AdminPermissions::PMB_QUEUE_KESEHATAN . '|' . AdminPermissions::PMB_ONLINE_KESEHATAN_MANAGE);

        // Route::controller(AdminTesKesehatanPemeriksaanController::class)
        //     ->prefix('tes-kesehatan-pemeriksaan')
        //     ->name('tes-kesehatan.pemeriksaan.')
        //     ->middleware(['role:Super Admin'])
        //     ->group(function () {
        //         Route::get('/', 'index')->name('index');               // daftar semua hasil anamnesa yang siap diperiksa
        //         Route::get('/create/{anamnesaId}', 'create')->name('create'); // form pemeriksaan baru
        //         Route::post('/', 'store')->name('store');               // simpan hasil pemeriksaan
        //         Route::get('/{id}', 'show')->name('show');              // lihat detail pemeriksaan
        //         Route::get('/{id}/edit', 'edit')->name('edit');         // edit hasil pemeriksaan
        //         Route::put('/{id}', 'update')->name('update');          // update hasil pemeriksaan
        //         Route::delete('/{id}', 'destroy')->name('destroy');     // hapus pemeriksaan
        //     });

        /** ------------------------------
         * 🎤 Wawancara PMB
         * ----------------------------- */
        Route::prefix('wawancara-pmb')
            ->name('wawancara.')
            ->middleware(['permission:' . AdminPermissions::WAWANCARA_REVIEW . '|' . AdminPermissions::PMB_ONLINE_WAWANCARA_MANAGE . '|' . AdminPermissions::PMB_QUEUE_WAWANCARA])
            ->controller(WawancaraReviewController::class)
            ->group(function () {

                Route::get('/', 'index')->name('index');

                Route::get('/{wawancara}', 'show')->name('show');

                // ✅ GET → FORM REVIEW
                Route::get('/{wawancara}/review', 'edit')->name('review.form');

                // ✅ POST → SIMPAN REVIEW
                Route::post('/{wawancara}/review', 'review')->name('review');

                // 🔒 LOCK
                Route::put('/{wawancara}/lock', 'lock')->name('lock');
            });
    });

Route::group([
    'as' => 'auth.'
], function () {
    Route::get('/login', [AuthController::class, 'viewLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'storeLogin'])->name('store.login');

    Route::get('/register', [AuthController::class, 'viewRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('store.register');
    Route::post('/auth/verify-account/{user}/update-phone', [AuthController::class, 'updatePhoneNumber'])
        ->name('update-phone');

    Route::get('/verifikasi-akun/{user}', [AuthController::class, 'viewVerifyAccount'])->name('verify-account');
    Route::post('/verifikasi-akun/{user}', [AuthController::class, 'storeVerifyAccount'])->name('store.verify-account');
    Route::get('/verifikasi-akun/kirim-ulang-kode/{user}', [AuthController::class, 'resendVerifyCode'])->name('resend-code');

    Route::get('/template-email', function () {
        $verifyCode = 123123;
        $user_name = 'PMB BOGOR HUSADA';
        $user_id = Str::uuid();
        $prodi = 'Contoh Program Studi';

        return view('templates.mail.verify-code', compact('verifyCode', 'user_name', 'user_id', 'prodi'));
    });

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::group([
    'prefix' => 'dashboard',
    'as' => 'dashboard.',
    'middleware' => 'auth'
], function () {

    // =======================
    // 🏠 Dashboard & Profil
    // =======================
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/seleksi-tes-pmb', [DashboardController::class, 'selection'])->name('selection.index');

    Route::get('/profil-siswa', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profil-siswa', [ProfileController::class, 'update'])->name('profile.update');

    // =======================
    // 📁 Update Berkas
    // =======================
    Route::get('/update-berkas', [ProfileController::class, 'editBerkas'])->name('profile.editBerkas');
    Route::post('/update-berkas', [ProfileController::class, 'updatedata'])->name('profile.berkasUpdate');

    // =======================
    // 💳 Pembayaran
    // =======================
    Route::get('/update-bukti', [ProfileController::class, 'updatePayment'])->name('profile.updatePayment');
    Route::post('/update-bukti', [ProfileController::class, 'updatedataPembayaran'])->name('profile.updatedataPembayaran');

    // =======================
    // 🧾 Cetak Kartu
    // =======================
    Route::get('/cetak-kartu', [ProfileController::class, 'cetakKartu'])->name('profile.cetakKartu');
    Route::get('/cetak-kartu-pmb', [ProfileController::class, 'cetak'])->name('cetak.kartu.pmb');
    Route::get('/antrian-pmb', [PmbQueueController::class, 'index'])->name('pmb-queue.index');
    Route::get('/antrian-pmb/{queue}/cetak', [PmbQueueController::class, 'print'])->name('pmb-queue.print');
    Route::get('/antrian-pmb/{queue}', [PmbQueueController::class, 'show'])->name('pmb-queue.show');

    // =======================
    // ✍️ Tes Tulis
    // =======================

    Route::get('/tes-tulis', [UserTesTulisController::class, 'index'])->name('user.tes-tulis.index');
    Route::get('/tes-tulis/{id}', [UserTesTulisController::class, 'show'])->name('tes-tulis.show');
    Route::post('/tes-tulis/{id}/submit', [UserTesTulisController::class, 'submit'])->name('tes-tulis.submit');
    Route::get('/tes-tulis/{id}/result', [UserTesTulisController::class, 'result'])->name('tes-tulis.result');

    // =======================
    // 🩺 Tes Kesehatan (NEW)
    // =======================

    Route::prefix('tes-kesehatan')->as('tes-kesehatan.')->group(function () {
        Route::get('/anamnesa', [TesKesehatanAnamnesaController::class, 'create'])->name('anamnesa.create');
        Route::post('/anamnesa', [TesKesehatanAnamnesaController::class, 'store'])->name('anamnesa.store');
        Route::post('/surat-kesehatan', [TesKesehatanAnamnesaController::class, 'uploadHealthLetter'])->name('surat.store');
        Route::get('/hasil', [TesKesehatanAnamnesaController::class, 'show'])->name('anamnesa.show');
        Route::get('/hasil/cetak', [TesKesehatanAnamnesaController::class, 'cetak'])->name('anamnesa.cetak');
    });

    // =======================
    // 📍 Wilayah (Ajax)
    // =======================
    Route::get('get-kabupaten/{provinsiId}', [ProfileController::class, 'getKabupaten'])->name('get.kabupaten');
    Route::get('get-kecamatan/{kabupatenId}', [ProfileController::class, 'getKecamatan'])->name('get.kecamatan');
    Route::get('get-kelurahan/{kecamatanId}', [ProfileController::class, 'getKelurahan'])->name('get.kelurahan');
    Route::get('wawancara', [WawancaraMahasiswaController::class, 'index'])
        ->name('wawancara.index');
    Route::get('wawancara/show', [WawancaraMahasiswaController::class, 'show'])
        ->name('wawancara.show');
    Route::get('wawancara/cetak', [WawancaraMahasiswaController::class, 'cetak'])
        ->name('wawancara.cetak');
    Route::post('wawancara/submit', [WawancaraMahasiswaController::class, 'submit'])
        ->name('wawancara.submit');
});
