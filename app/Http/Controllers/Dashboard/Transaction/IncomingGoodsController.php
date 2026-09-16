<?php

namespace App\Http\Controllers\Dashboard\Transaction;

use App\Models\Item;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\IncomingGoods;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Transaction\IncomingGoodsExport;
use App\Http\Requests\Transaction\IncomingGoodsRequest;

class IncomingGoodsController extends Controller
{
    public function index()
    {
        $data = IncomingGoods::filterByUser()
            ->whereDateRange('incoming_date', request()->query('start_date'), request()->query('end_date'))
            ->latest()->get();
            
        return view('dashboard.pages.transaction.incoming.index', compact('data'));
    }

    public function filter()
    {
        $action_url = route('dashboard.transaction.incoming.index');
        return view('dashboard.components.filter-date-range', compact('action_url'));
    }
    
    public function create()
    {
        $suppliers = Supplier::filterByUser()->orderBy('name', 'ASC')->get();
        $items = Item::filterByUser()->orderBy('name', 'ASC')->get();

        return view('dashboard.pages.transaction.incoming.create', compact('suppliers', 'items'));
    }   

    public function store(IncomingGoodsRequest $request)
    {
        $requestDTO = $request->validated();

        DB::beginTransaction();
        try {
            $requestDTO['code'] = (new IncomingGoods)->generateUniqueCode(IncomingGoods::UNIQUE_CODE_PREFIX);
            $requestDTO['user_id'] = auth()->user()->uuid;
            $items = $requestDTO['items'];
            unset($requestDTO['items']);

            $createdIncomingGoods = IncomingGoods::create($requestDTO);

            foreach ($items as $item) {
                $itemData = Item::where('uuid', $item['item_id'])->first();
                $createdIncomingGoods->items()->create([
                    'item_id' => $item['item_id'],
                    'initial_stock' => $itemData->stock,
                    'total_stock' => $item['total_stock'],
                    'current_stock' => $itemData->stock + $item['total_stock']
                ]);

                $itemData->update([
                    'stock' => $itemData->stock + $item['total_stock']
                ]);
            }

            DB::commit();
            return redirect()->route('dashboard.transaction.incoming.index')->with('toastSuccess', __('crud.created', ['name' => 'Barang Masuk']));
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('dashboard.transaction.incoming.create')->with('toastError', __('crud.error_create', ['name' => 'Barang Masuk']))->withInput();
        }
    }

    public function delete(string $uuid)
    {

        DB::beginTransaction();
        try {
            $incomingGoods = IncomingGoods::where('uuid', $uuid)->first();
            if (is_null($incomingGoods)) {
                return redirect()->route('dashboard.transaction.incoming.index', $uuid)->with('toastError', __('crud.not_found', ['name' => 'Barang Masuk']));
            }

            foreach ($incomingGoods->items as $item) {
                $incomingGoodsItem = $item->item;
                $incomingGoodsItem->update([
                    'stock' => $incomingGoodsItem->stock - $item->total_stock
                ]);
            }
            
            $incomingGoods->delete();

            DB::commit();
            return redirect()->route('dashboard.transaction.incoming.index')->with('toastSuccess', __('crud.deleted', ['name' => 'Barang Masuk']));
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('dashboard.transaction.incoming.index', $uuid)->with('toastError', __('crud.error_delete', ['name' => 'Barang Masuk']))->withInput();
        }
    }

    public function exportToExcel()
    {
        $currentDate = now()->format('Ymd');
        $data = IncomingGoods::with('items')->filterByUser()
            ->whereDateRange('incoming_date', request()->query('start_date'), request()->query('end_date'))
            ->when(auth()->user()->role == User::ADMIN_ROLE, function ($query) {
                $query->orderBy('user_id', 'ASC');
            })->orderBy('code', 'ASC')->get();

        return Excel::download(new IncomingGoodsExport($data), "{$currentDate}-stockflow-barang-masuk.xlsx");
    }
}
