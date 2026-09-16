<?php

namespace App\Http\Controllers\Dashboard\MasterData;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterData\CustomerExport;
use App\Http\Requests\Customer\CustomerRequest;

class CustomerController extends Controller
{
    public function index()
    {
        $data = Customer::filterByUser()->latest()->get();
        return view('dashboard.pages.master-data.customer.index', compact('data'));
    }

    public function create()
    {
        $title = 'Form Tambah Pelanggan';
        $action_url = route('dashboard.customer.store');
        $method = 'POST';
        $data = new Customer();

        return view('dashboard.pages.master-data.customer.form', compact('title', 'action_url', 'method', 'data'));
    }   

    public function store(CustomerRequest $request)
    {
        $requestDTO = $request->validated();
        try {
            $requestDTO['code'] = (new Customer)->generateUniqueCode(Customer::UNIQUE_CODE_PREFIX);
            $requestDTO['user_id'] = auth()->user()->uuid;
            Customer::create($requestDTO);

            return redirect()->route('dashboard.customer.index')->with('toastSuccess', __('crud.created', ['name' => 'Pelanggan']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.customer.create')->with('toastError', __('crud.error_create', ['name' => 'Pelanggan']))->withInput();
        }
    }

    public function edit(string $uuid)
    {
        $data = Customer::where('uuid', $uuid)->first();
        if (is_null($data)) return redirect()->route('dashboard.customer.index')->with('toastError', __('crud.not_found', ['name' => 'Pelanggan']));

        $title = 'Form Edit Pelanggan';
        $action_url = route('dashboard.customer.update', $uuid);
        $method = 'PUT';

        return view('dashboard.pages.master-data.customer.form', compact("data", 'title', 'action_url', 'method'));
    }

    public function update(CustomerRequest $request, string $uuid)
    {
        $requestDTO = $request->validated();
        try {
            Customer::where('uuid', $uuid)->update($requestDTO);
            return redirect()->route('dashboard.customer.index')->with('toastSuccess', __('crud.updated', ['name' => 'Pelanggan']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.customer.edit', $uuid)->with('toastError', __('crud.error_update', ['name' => 'Pelanggan']))->withInput();
        }
    }

    public function delete(string $uuid)
    {
        try {
            Customer::where('uuid', $uuid)->delete();
            return redirect()->route('dashboard.customer.index')->with('toastSuccess', __('crud.deleted', ['name' => 'Pelanggan']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.customer.edit', $uuid)->with('toastError', __('crud.error_delete', ['name' => 'Pelanggan']))->withInput();
        }
    }

    public function exportToExcel()
    {
        $currentDate = now()->format('Ymd');
        $data = Customer::filterByUser()
            ->when(auth()->user()->role == User::ADMIN_ROLE, function ($query) {
                $query->orderBy('user_id', 'ASC');
            })->orderBy('code', 'ASC')->get();

        return Excel::download(new CustomerExport($data), "{$currentDate}-stockflow-pelanggan.xlsx");
    }
}
