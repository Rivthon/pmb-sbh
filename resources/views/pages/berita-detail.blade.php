@extends('layouts.app_berita')

@section('content')
<!-- Sections: Start -->
<div data-bs-spy="scroll" class="scrollspy-example">
    <section id="berita-detail" class="section-py berita-detail">
        <div class="container mx-auto px-4 py-5">
            <div id="berita-content" class="bg-white rounded shadow p-4 flex justify-center items-center min-h-[120px]">
                <div class="flex flex-col items-center">
                    <span class="sneat-spinner-border sneat-spinner-border-primary mb-2" role="status"
                        aria-label="Loading"></span>
                    <span>Memuat konten berita...</span>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- Sections: End -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
            const slug = @json($slug);
            const container = document.getElementById('berita-content');

            fetch(`/api/berita/${slug}`)
                .then(res => res.json())
                .then(post => {
                    // Handle jika berita tidak ditemukan
                    if (post.message === 'Not found') {
                        container.innerHTML = `
                            <div class="text-danger text-center">Berita tidak ditemukan.</div>
                        `;
                        return;
                    }

                    // Format tanggal
                    const tanggal = new Date(post.date).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });

                    // Modifikasi tampilan gambar: float kiri, ukuran tetap proporsional
                    const styledContent = post.content.rendered.replace(
                        /<img([^>]*)>/g,
                        `<img$1
                            style="
                                float: left;
                                margin: 10px 24px 10px 0;
                                border-radius: 8px;
                                max-width: 220px;
                                width: 220px;
                                height: 220px;
                                object-fit: cover;
                            ">
                        `
                    );

                    // Render konten ke dalam container
                    container.innerHTML = `
                        <p class="text-xs text-gray-500 mb-4">Tanggal ${tanggal}</p>
                        <div class="prose max-w-none text-justify w-full mb-6" style="overflow: hidden; min-height: 230px;">
                            ${styledContent}
                        </div>
                        <a href="/" class="btn btn-link px-0">← Kembali ke Berita</a>
                    `;
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = `
                        <div class="text-danger text-center">Terjadi kesalahan saat memuat berita.</div>
                    `;
                });
        });
</script>
@endsection