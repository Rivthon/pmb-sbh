@extends('templates.pdf.layouts.master')
@section('title', 'Lapooran Stok Barang')

@push('style')
    <style>
        #table-data, #table-data th, #table-data td {
            border: 1px solid #000;
        }

        #table-data {
            border-collapse: collapse;
        }

        #table-data th {
            height: 50px;
        }

        #table-data td {
            padding: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="bg-primary clr-white" style="padding: 0 20px; margin-bottom: 30px;">
        <table class="w-100 ">
            <tr>
                <td class="vertical-middle">
                    <h2 class="">{{ config('app.name') }}</h2>
                </td>
                <td class="vertical-middle text-right">
                    {{ now()->format('d-m-Y H:i:s') }}
                </td>
            </tr>
        </table>
    </div>

    <h2 class="text-center">Struk Pembayaran <br> {{$data->user->name }}</h2>

    @if (!is_null($data->user?->address))
        <p class="text-center">{{ $data->user->address }}</p>
    @endif

    <hr>

    <table>
        @if(!is_null($data->customer_id))
            <tr>
                <td>Pelanggan</td>
                <td>: {{ $data->customer->name }}</td>
            </tr>
        @endif

            <tr>
                <td>Tanggal</td>
                <td>: {{ $data->created_at?->format('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <td>Kode</td>
                <td>: {{ $data->code }}</td>
            </tr>
    </table>

    <hr>

    <table class="w-100">
        @foreach ($data->items as $item)
            <tr>
                <td colspan="2">
                    <strong>{{ $item->item->name }}</strong>
                </td>
            </tr>
            <tr>
                <td>
                    {{ $item->quantity }} x Rp {{ number_format($item->price) }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($item->quantity * $item->price) }}
                </td>
            </tr>
        @endforeach

        <tr>
            <td colspan="2">
                <hr>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Total</strong>
            </td>
            <td class="text-right">
                Rp {{ number_format($data->subtotal) }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Bayar (Cash)</strong>
            </td>
            <td class="text-right">
                Rp {{ number_format($data->total_payment) }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Kembali</strong>
            </td>
            <td class="text-right">
                Rp {{ number_format($data->return_payment) }}
            </td>
        </tr>
    </table>
    
@endsection