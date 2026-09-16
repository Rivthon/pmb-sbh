@if(isset($data) && $data instanceof \Illuminate\Pagination\AbstractPaginator)
{{ $data->withQueryString()->onEachSide(1)->links('templates.sneat') }}
@endif
