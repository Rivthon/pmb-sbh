@forelse ($data as $index => $mahasiswa)
    @include('admin_dashboard.pages.mahasiswa._row', [
        'mahasiswa' => $mahasiswa,
        'index' => $index
    ])
@empty
    <tr>
        <td colspan="8" class="text-center py-5">
            @include('admin_dashboard.components.empty-state', [
                'title' => 'Tidak Ada Data',
                'description' => 'Tidak ada data mahasiswa PMB yang ditemukan.',
                'icon' => 'bx-group'
            ])
        </td>
    </tr>
@endforelse
