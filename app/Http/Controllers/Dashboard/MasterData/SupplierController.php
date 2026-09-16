<?php

namespace App\Http\Controllers\Dashboard\MasterData;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterData\SupplierExport;
use App\Http\Requests\Supplier\SupplierRequest;

class SupplierController extends Controller
{
    public function index()
    {
        $data = Supplier::filterByUser()->latest()->get();
        return view('dashboard.pages.master-data.supplier.index', compact('data'));
    }

    public function create()
    {
        $title = 'Form Tambah Supplier';
        $action_url = route('dashboard.supplier.store');
        $method = 'POST';
        $data = new Supplier();

        return view('dashboard.pages.master-data.supplier.form', compact('title', 'action_url', 'method', 'data'));
    }   

    public function store(SupplierRequest $request)
    {
        $requestDTO = $request->validated();
        try {
            $requestDTO['code'] = (new Supplier)->generateUniqueCode(Supplier::UNIQUE_CODE_PREFIX);
            $requestDTO['user_id'] = auth()->user()->uuid;
            Supplier::create($requestDTO);

            return redirect()->route('dashboard.supplier.index')->with('toastSuccess', __('crud.created', ['name' => 'Supplier']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.supplier.create')->with('toastError', __('crud.error_create', ['name' => 'Supplier']))->withInput();
        }
    }

    public function edit(string $uuid)
    {
        $data = Supplier::where('uuid', $uuid)->first();
        if (is_null($data)) return redirect()->route('dashboard.supplier.index')->with('toastError', __('crud.not_found', ['name' => 'Supplier']));

        $title = 'Form Edit Supplier';
        $action_url = route('dashboard.supplier.update', $uuid);
        $method = 'PUT';

        return view('dashboard.pages.master-data.supplier.form', compact("data", 'title', 'action_url', 'method'));
    }

    public function update(SupplierRequest $request, string $uuid)
    {
        $requestDTO = $request->validated();
        try {
            Supplier::where('uuid', $uuid)->update($requestDTO);
            return redirect()->route('dashboard.supplier.index')->with('toastSuccess', __('crud.updated', ['name' => 'Supplier']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.supplier.edit', $uuid)->with('toastError', __('crud.error_update', ['name' => 'Supplier']))->withInput();
        }
    }

    public function delete(string $uuid)
    {
        try {
            Supplier::where('uuid', $uuid)->delete();
            return redirect()->route('dashboard.supplier.index')->with('toastSuccess', __('crud.deleted', ['name' => 'Supplier']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.supplier.index', $uuid)->with('toastError', __('crud.error_delete', ['name' => 'Supplier']))->withInput();
        }
    }

    public function exportToExcel()
    {
        $currentDate = now()->format('Ymd');
        $data = Supplier::filterByUser()
            ->when(auth()->user()->role == User::ADMIN_ROLE, function ($query) {
                $query->orderBy('user_id', 'ASC');
            })->orderBy('code', 'ASC')->get();

        return Excel::download(new SupplierExport($data), "{$currentDate}-stockflow-supplier.xlsx");
    }
}
