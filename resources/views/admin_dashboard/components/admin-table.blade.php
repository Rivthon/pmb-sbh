<div class="table-responsive admin-table-wrapper">
    <table class="table admin-table table-hover align-middle" @if($id ?? false) id="{{ $id }}" @endif>
        <thead>
            <tr>
                @foreach($headers as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody @if($bodyId ?? false) id="{{ $bodyId }}" @endif>
            {{ $slot }}
        </tbody>
    </table>
</div>
