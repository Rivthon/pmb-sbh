@extends('dashboard.layout.master')
@section('title', 'Form Tambah Barang Masuk')

@section('content')
    <!-- Form Supplier -->
    <div class="card">
        <div class="d-flex align-items-center justify-content-between pe-4">
            <h5 class="card-header mb-0">Form Tambah Barang Masuk</h5>
        </div>
        <div class="card-body pt-0">
            <form action="{{ route("dashboard.transaction.incoming.store") }}" method="POST" class="row">
                @csrf
                <div class="col-md-6 mb-3">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-select select2 @error('supplier_id') is-invalid @enderror">
                        <option value="">Pilih Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->uuid }}" @selected(old('supplier_id') == $supplier->uuid)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    <i class="d-block">
                        <small>*Kosongkan saja jika tidak ada supplier</small>
                    </i>
                    @error('supplier_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="incoming_date" class="form-label required">Tanggal</label>
                    <input type="date" class="form-control @error('incoming_date') is-invalid @enderror" name="incoming_date" id="incoming_date" value="{{ old('incoming_date', now()->format('Y-m-d')) }}" placeholder="Masukkan Tanggal" required>
                    @error('incoming_date')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea name="notes" id="notes" rows="4" class="form-control" placeholder="Masukkan Catatan Tambahan">{{ old('notes') }}</textarea>
                    @error('notes')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-12">
                    <hr>
                    <div class="my-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Data Barang</h5>
                        <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Tambah Barang" id="add-item">
                            <i class="bx bx-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 mb-3 d-flex flex-column" id="items-box" style="row-gap: 10px;">
                    @php
                        $defaultItem = [
                            [
                                'item_id' => NULL,
                                'total_stock' => NULL
                            ]
                        ];
                    @endphp

                    @foreach (old('items', $defaultItem) as $index => $oldItem)
                        <div class="items row" style="row-gap: 5px;">
                            <div class="col-12 col-md-6">
                                <label for="" class="form-label required">Barang</label>
                                <select name="items[{{ $index }}][item_id]" id="" class='form-select @error("items.{$index}.item_id") is-invalid @enderror' required>
                                    <option value="">Pilih Barang</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->uuid }}" @selected($item->uuid == $oldItem['item_id'])>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error("items.{$index}.item_id")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-10 col-md-5">
                                <label for="" class="form-label required">Jumlah</label>
                                <input type="number" name="items[{{ $index }}][total_stock]" id="" class='form-control @error("items.{$index}.total_stock") is-invalid @enderror' value="{{ $oldItem['total_stock'] }}" placeholder="0" min="1" required>
                                @error("items.{$index}.total_stock")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger btn-icon delete-item"z>
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    @error('items')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- End Form Supplier -->
@endsection

@push('script')
    <script>
        function generateDynamicItem(elementWrapper, childElement) {
            const clonedElement = $(elementWrapper).find(childElement).eq(0).clone();
            $(clonedElement).find('select, input').val('');
            $(elementWrapper).append(clonedElement);
        }

        function renameIndexArrayFormElement(elementWrapper, childElement) {
            $(elementWrapper).find(childElement).each(function (index) {
                $(this).find('input, select').each(function (i) {
                    const nameAttribute = $(this).attr('name');
                    const splittedNameAttribute = $(this).attr('name').split('[');
                    const newAttributeIndex = `${index}]`;
                    splittedNameAttribute[1] = newAttributeIndex;
                    
                    const newNameAttribute = splittedNameAttribute.join('[');
                    $(this).attr('name', newNameAttribute);
                })
            })
        }

        function removeItem(elementWrapper, deletedElement, childElement){
            const totalChildElement = $(elementWrapper).find(childElement).length;
            if (totalChildElement == 1) {
                $(deletedElement).find('input, select').val('');
            } else {
                $(deletedElement).remove();
            }
        }

        $(document).ready(function () {
            $("#add-item").off('click').on('click', function (event) {
                generateDynamicItem("#items-box", ".items");
                renameIndexArrayFormElement('#items-box', '.items');
            })

            $(document).off('click', '.delete-item').on('click', '.delete-item', function (event) {
                removeItem("#items-box", $(this).parents('.items'), '.items');
                renameIndexArrayFormElement('#items-box', '.items');
            })
        });
    </script>
@endpush