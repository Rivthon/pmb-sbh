@extends('admin_dashboard.layout.master')
@section('title', 'Konten Informasi Landing Page')

@section('content')
<div class="container">
    <div class="mb-4">
        <h4 class="fw-bold mb-1"><i class="bx bx-detail text-primary me-2"></i>Konten Informasi Landing Page</h4>
        <p class="text-muted mb-0">Kelola kartu keunggulan kampus dan informasi tambahan biaya kuliah.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @foreach ([['title' => 'Keunggulan Kampus', 'cards' => $advantageCards, 'items' => false], ['title' => 'Informasi Biaya', 'cards' => $costCards, 'items' => true]] as $group)
        <div class="d-flex align-items-center gap-2 mt-4 mb-3"><h5 class="fw-bold mb-0">{{ $group['title'] }}</h5><span class="badge bg-label-primary">{{ $group['cards']->count() }} kartu</span></div>
        <div class="row g-4">
            @foreach ($group['cards'] as $card)
                <div class="col-xl-6">
                    <div class="card h-100">
                        <form method="POST" action="{{ route('admin.landing-info-cards.update', $card) }}">
                            @csrf @method('PUT')
                            <div class="card-header border-bottom d-flex align-items-center gap-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $card->color }} bg-opacity-10 text-{{ $card->color }}" style="width:48px;height:48px"><i class="{{ $card->icon }} fs-4"></i></span>
                                <div><strong>{{ $card->title }}</strong><div class="small text-muted">Urutan {{ $card->sort_order }}</div></div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-7"><label class="form-label">Judul</label><input class="form-control" name="title" value="{{ old('title', $card->title) }}" maxlength="120" required></div>
                                    <div class="col-md-5"><label class="form-label">Ikon Boxicons</label><input class="form-control" name="icon" value="{{ old('icon', $card->icon) }}" required></div>
                                    @if ($group['items'])
                                        <div class="col-12"><label class="form-label">Daftar Poin</label><textarea class="form-control" name="items_text" rows="6" required>{{ old('items_text', implode("\n", $card->items ?? [])) }}</textarea><div class="form-text">Tulis satu poin per baris.</div></div>
                                    @else
                                        <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="description" rows="3" maxlength="500" required>{{ old('description', $card->description) }}</textarea></div>
                                    @endif
                                    <div class="col-md-5"><label class="form-label">Warna</label><select class="form-select" name="color">@foreach (['warning'=>'Kuning','success'=>'Hijau','info'=>'Biru Muda','danger'=>'Merah','primary'=>'Ungu','dark'=>'Gelap','secondary'=>'Abu-abu'] as $value=>$label)<option value="{{ $value }}" @selected(old('color', $card->color)===$value)>{{ $label }}</option>@endforeach</select></div>
                                    <div class="col-md-3"><label class="form-label">Urutan</label><input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $card->sort_order) }}" min="0" max="999" required></div>
                                    <div class="col-md-4 d-flex align-items-end pb-2"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="info-active-{{ $card->id }}" @checked(old('is_active', $card->is_active))><label class="form-check-label" for="info-active-{{ $card->id }}">Tampilkan</label></div></div>
                                </div>
                            </div>
                            <div class="card-footer border-top text-end"><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Perubahan</button></div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endsection
