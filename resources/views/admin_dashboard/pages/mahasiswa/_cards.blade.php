@forelse ($data as $index => $mahasiswa)
    @include('admin_dashboard.pages.mahasiswa._card', [
        'mahasiswa' => $mahasiswa,
        'index' => $index
    ])
@empty
    <div class="mahasiswa-mobile-empty">
        @include('admin_dashboard.components.empty-state', [
            'title' => 'Tidak Ada Data',
            'description' => 'Tidak ada data mahasiswa PMB yang ditemukan.',
            'icon' => 'bx-group'
        ])
    </div>
@endforelse
