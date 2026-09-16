@extends('dashboard.layout.master')
@section('title', 'Form Tambah Transaksi Penjualan')

@section('content')
    <!-- Form Supplier -->
    <div class="card">
        <div class="d-flex align-items-center justify-content-between pe-4">
            <h5 class="card-header mb-0">Form Tambah Transaksi Penjualan</h5>
        </div>
        <div class="card-body pt-0">
            <form action="{{ route("dashboard.transaction.sale.store") }}" method="POST" class="row justify-content-start">
                @csrf
                <div class="col-12 mb-3">
                    <label for="customer_id" class="form-label">Pelanggan</label>
                    <select name="customer_id" id="customer_id" class="form-select select2 @error('customer_id') is-invalid @enderror">
                        <option value="">Pilih Pelanggan</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->uuid }}" @selected(old('customer_id') == $customer->uuid)>{{ "{$customer->name} [{$customer->code}]" }}</option>
                        @endforeach
                    </select>
                    <i class="d-block">
                        <small>*Kosongkan saja jika tidak ada pelanggan</small>
                    </i>
                    @error('customer_id')
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
                                'quantity' => NULL,
                                'price' => 0
                            ]
                        ];
                    @endphp

                    @foreach (old('items', $defaultItem) as $index => $oldItem)
                        <div class="items row" style="row-gap: 5px;">
                            <div class="col-12 col-md-5">
                                <label for="" class="form-label required">Barang</label>
                                <select name="items[{{ $index }}][item_id]" id="" data-type="item_id" class='form-select @error("items.{$index}.item_id") is-invalid @enderror' required>
                                    <option value="">Pilih Barang</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->uuid }}" @selected($item->uuid == $oldItem['item_id']) data-price="{{ $item->selling_price }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error("items.{$index}.item_id")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="" class="form-label required">Jumlah (Qty)</label>
                                <input type="number" name="items[{{ $index }}][quantity]" data-type="quantity" id="" class='form-control @error("items.{$index}.quantity") is-invalid @enderror' value="{{ $oldItem['quantity'] }}" placeholder="0" min="1" required>
                                @error("items.{$index}.quantity")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-9 col-md-3">
                                <label for="" class="form-label required">Harga</label>
                                <input type="number" name="items[{{ $index }}][price]" data-type="price" id="" class='form-control @error("items.{$index}.price") is-invalid @enderror' value="{{ $oldItem['price'] }}" placeholder="0" min="1" readonly>
                                @error("items.{$index}.price")
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
                    <hr>
                </div>
                <div class="col-12 mb-3">
                    <label for="total_payment" class="form-label required">Nominal Pembayaran</label>
                    <input type="number" class="form-control @error('total_payment') is-invalid @enderror" name="total_payment" id="total_payment" value="{{ old('total_payment') }}" placeholder="0" min="1" required>
                    @error('total_payment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <div class="d-flex align-items-start flex-column" style="gap: 10px;">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <h5 class="mb-0">Nominal Pembayaran</h5>
                            <h5 class="mb-0" id="total-payment-text" style="font-weight: normal">0</h5>
                        </div>
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <h5 class="mb-0">Subtotal</h5>
                            <h5 class="mb-0" id="subtotal-text" style="font-weight: normal">0</h5>
                        </div>
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <h5 class="mb-0">Kembalian</h5>
                            <h5 class="mb-0" id="return-payment-text" style="font-weight: normal">0</h5>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2">
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

        function setDynamicPrice(selectItemElement, priceElement) {
            const price = $(selectItemElement).children("option:selected").data('price');
            const finalPrice = Number(price || 0);
            $(priceElement).val(finalPrice)
        }

        function calculatePayment() {
            let subtotal = 0;
            
            $("#items-box").find('.items').each(function (index) {
                const quantity = Number($(this).find('input[data-type="quantity"]').val() || 0);
                const price = Number($(this).find('input[data-type="price"]').val() || 0);

                subtotal += (quantity * price);
            });

            const total_payment = Number($("input#total_payment").val() || 0);
            const return_payment = total_payment - subtotal;

            $("#total-payment-text").text(total_payment);
            $("#subtotal-text").text(subtotal);
            $("#return-payment-text").text(return_payment);
        }

        $(document).ready(function () {
            calculatePayment();

            $("#add-item").off('click').on('click', function (event) {
                generateDynamicItem("#items-box", ".items");
                renameIndexArrayFormElement('#items-box', '.items');
            })

            $(document).off('click', '.delete-item').on('click', '.delete-item', function (event) {
                removeItem("#items-box", $(this).parents('.items'), '.items');
                renameIndexArrayFormElement('#items-box', '.items');
                calculatePayment();
            });

            $(document).off('change', 'select[data-type="item_id"]').on('change', 'select[data-type="item_id"]', function (event) {
                setDynamicPrice(
                    $(this),
                    $(this).parents('.items').find('input[data-type="price"]'),
                )

                calculatePayment();
            });

            $(document).off('keyup', 'input[data-type="quantity"]').on('keyup', 'input[data-type="quantity"]', function (event) {
                calculatePayment();
            })

            $("input#total_payment").keyup(function (event) {
                calculatePayment();
            })

        });
    </script>
@endpush