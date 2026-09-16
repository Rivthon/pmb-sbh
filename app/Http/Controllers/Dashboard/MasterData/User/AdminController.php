<?php

namespace App\Http\Controllers\Dashboard\MasterData\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterData\UserExport;
use App\Services\MasterData\UserService;
use App\Http\Requests\Users\CreateUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;

class AdminController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}
    
    public function index()
    {
        $users = $this->userService->listUsers([
            'role' => User::ADMIN_ROLE
        ]);

        return view('dashboard.pages.master-data.administrator.index', compact('users'));
    }

    public function create()
    {
        $title = 'Form Tambah Administrator';
        $action_url = route('dashboard.users.administrator.store');
        $method = 'POST';
        $data = new User;

        return view('dashboard.pages.master-data.administrator.form', compact('title', 'action_url', 'method', 'data'));
    }

    public function store(CreateUserRequest $request)
    {
        $requestDTO = $request->validated();
        $requestDTO['role'] = User::ADMIN_ROLE;
        $requestDTO['email_verified_at'] = now();

        $createUserResponse = $this->userService->createUser($requestDTO);
        return $createUserResponse->success
            ? redirect()->route('dashboard.users.administrator.index')->with('toastSuccess', $createUserResponse->message)
            : redirect()->route('dashboard.users.administrator.create')->with('toastError', $createUserResponse->message)->withInput();
    }

    public function edit(string $uuid)
    {
        $getUserResponse = $this->userService->findUserByUUID($uuid);
        if (!$getUserResponse->success) return redirect()->route('dashboard.users.administrator.index')->with('toastError', $getUserResponse->message);

        $title = 'Form Edit Administrator';
        $action_url = route('dashboard.users.administrator.update', $uuid);
        $method = 'PUT';
        $data = $getUserResponse->data;

        return view('dashboard.pages.master-data.administrator.form', compact('title', 'action_url', 'method', 'data'));

    }

    public function update(UpdateUserRequest $request, string $uuid)
    {
        $requestDTO = $request->validated();
        $updateUserResponse = $this->userService->updateUser($requestDTO, $uuid);

        return $updateUserResponse->success
            ? redirect()->route('dashboard.users.administrator.index')->with('toastSuccess', $updateUserResponse->message)
            : redirect()->route('dashboard.users.administrator.edit', $uuid)->with('toastError', $updateUserResponse->message)->withInput();
    }

    public function delete(string $uuid)
    {
        $deleteUserResponse = $this->userService->deleteUser($uuid);
        return redirect()->route('dashboard.users.administrator.index')->with($deleteUserResponse->success ? 'toastSuccess' : 'toastError', $deleteUserResponse->message);
    }

    public function exportToExcel()
    {
        $currentDate = now()->format('Ymd');
        $data = User::where('role', User::ADMIN_ROLE)->orderBy('code', 'ASC')->get();

        return Excel::download(new UserExport($data, User::ADMIN_ROLE), "{$currentDate}-stockflow-administrator.xlsx");
    }
}
