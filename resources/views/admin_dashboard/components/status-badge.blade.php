@php
$statusMap = [
    'aktif' => ['class' => 'status-aktif', 'icon' => 'bx-check-circle', 'label' => 'Aktif'],
    'active' => ['class' => 'status-aktif', 'icon' => 'bx-check-circle', 'label' => 'Active'],
    'nonaktif' => ['class' => 'status-nonaktif', 'icon' => 'bx-x-circle', 'label' => 'Non Aktif'],
    'inactive' => ['class' => 'status-nonaktif', 'icon' => 'bx-x-circle', 'label' => 'Inactive'],
    'pending' => ['class' => 'status-pending', 'icon' => 'bx-time', 'label' => 'Pending'],
    'menunggu' => ['class' => 'status-pending', 'icon' => 'bx-time', 'label' => 'Menunggu'],
    'lulus' => ['class' => 'status-lulus', 'icon' => 'bx-check-double', 'label' => 'Lulus'],
    'tidak_lulus' => ['class' => 'status-gagal', 'icon' => 'bx-x', 'label' => 'Tidak Lulus'],
    'verified' => ['class' => 'status-verified', 'icon' => 'bx-badge-check', 'label' => 'Terverifikasi'],
    'terverifikasi' => ['class' => 'status-verified', 'icon' => 'bx-badge-check', 'label' => 'Terverifikasi'],
    'ditolak' => ['class' => 'status-ditolak', 'icon' => 'bx-block', 'label' => 'Ditolak'],
    'belum_bayar' => ['class' => 'status-belum', 'icon' => 'bx-wallet', 'label' => 'Belum Bayar'],
    'sudah_bayar' => ['class' => 'status-lulus', 'icon' => 'bx-check-circle', 'label' => 'Sudah Bayar'],
    'review' => ['class' => 'status-review', 'icon' => 'bx-search', 'label' => 'Dalam Review'],
    'draft' => ['class' => 'status-draft', 'icon' => 'bx-edit', 'label' => 'Draft'],
];

$statusKey = strtolower($status ?? 'draft');
$info = $statusMap[$statusKey] ?? ['class' => 'status-draft', 'icon' => 'bx-info-circle', 'label' => ucfirst(str_replace('_', ' ', $statusKey))];
$displayLabel = $label ?? $info['label'];
@endphp

<span class="status-badge {{ $info['class'] }}">
    <i class="bx {{ $info['icon'] }}"></i>
    {{ $displayLabel }}
</span>
