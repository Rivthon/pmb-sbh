<?php

namespace App\Http\Controllers\Dashboard\MasterData\Item;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemCategory\ItemCategoryRequest;
use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $data = ItemCategory::filterByUser()->latest()->get();
        return view('dashboard.pages.master-data.item-category.index', compact('data'));
    }

    public function create()
    {
        $title = 'Form Tambah Kategori Barang';
        $action_url = route('dashboard.items.category.store');
        $method = 'POST';
        $data = new ItemCategory();

        return view('dashboard.pages.master-data.item-category.form', compact('title', 'action_url', 'method', 'data'));
    }   

    public function store(ItemCategoryRequest $request)
    {
        $requestDTO = $request->validated();
        try {
            $requestDTO['user_id'] = auth()->user()->uuid;
            ItemCategory::create($requestDTO);

            return redirect()->route('dashboard.items.category.index')->with('toastSuccess', __('crud.created', ['name' => 'Kategori Barang']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.items.category.create')->with('toastError', __('crud.error_create', ['name' => 'Kategori Barang']))->withInput();
        }
    }

    public function edit(string $uuid)
    {
        $data = ItemCategory::where('uuid', $uuid)->first();
        if (is_null($data)) return redirect()->route('dashboard.items.category.index')->with('toastError', __('crud.not_found', ['name' => 'Kategori Barang']));

        $title = 'Form Edit Kategori Barang';
        $action_url = route('dashboard.items.category.update', $uuid);
        $method = 'PUT';

        return view('dashboard.pages.master-data.item-category.form', compact("data", 'title', 'action_url', 'method'));
    }

    public function update(ItemCategoryRequest $request, string $uuid)
    {
        $requestDTO = $request->validated();
        try {
            ItemCategory::where('uuid', $uuid)->update($requestDTO);
            return redirect()->route('dashboard.items.category.index')->with('toastSuccess', __('crud.updated', ['name' => 'Kategori Barang']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.items.category.edit', $uuid)->with('toastError', __('crud.error_update', ['name' => 'Kategori Barang']))->withInput();
        }
    }

    public function delete(string $uuid)
    {
        try {
            ItemCategory::where('uuid', $uuid)->delete();
            return redirect()->route('dashboard.items.category.index')->with('toastSuccess', __('crud.deleted', ['name' => 'Kategori Barang']));
        } catch (\Throwable $th) {
            return redirect()->route('dashboard.items.category.edit', $uuid)->with('toastError', __('crud.error_delete', ['name' => 'Kategori Barang']))->withInput();
        }
    }
}
