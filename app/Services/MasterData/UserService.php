<?php

namespace App\Services\MasterData;

use App\Models\User;
use App\Models\Periode;
use App\Models\Gelombang;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Support\PmbStorage;
use App\Helpers\Responses\ObjectResponse;
use App\Traits\Utilities\HandleUploadedFile;

class UserService
{
    use HandleUploadedFile;

    public function listUsers($filters = [])
    {
        return User::when(!empty($filters['role']), function ($query) use ($filters) {
            $query->where('role', $filters['role']);
        })->when(!empty($filters['is_active']) && $filters['is_active'] == true, function ($query) use ($filters) {
            $query->isActive();
        })->latest()->get();
    }

    public function findUserByUUID(string $user_id)
    {
        $data = User::where('uuid', $user_id)->first();
        return !is_null($data)
            ? ObjectResponse::success(__('crud.fetched', ['name' => 'User']), 200, $data)
            : ObjectResponse::error(__('crud.not_found', ['name' => 'User']), 404);
    }

    public function createUser(array $userDTO)
    {
        try {
            // Password bawaan dibuat otomatis saat registrasi. Password plaintext
            // hanya dibawa sementara untuk email dan tidak disimpan ke database.
            $plainPassword = blank($userDTO['password'] ?? null)
                ? Str::random(10)
                : $userDTO['password'];

            // Upload gambar jika ada
            if (!empty($userDTO['image'])) {
                $userDTO['image'] = $this->uploadFile($userDTO['image'], User::FOLDER_NAME);
            }

            unset($userDTO['password_plaintext']);
            $userDTO['password'] = bcrypt($plainPassword);

            // Generate kode unik berdasarkan role
            $prefix = $userDTO['role'] == User::ADMIN_ROLE
                ? User::ADMIN_PREFIX_CODE
                : User::USER_PREFIX_CODE;
            $userDTO['code'] = (new User)->generateUniqueCode($prefix);

            // Ambil periode aktif
            if ($activePeriode = Periode::where('status_periode', 'aktif')->first()) {
                $userDTO['periode_id'] = $activePeriode->id;
            }

            // Ambil gelombang aktif
            if ($activeGelombang = Gelombang::where('status_gelombang', 'aktif')->first()) {
                $userDTO['gelombang_id'] = $activeGelombang->id;
            }

            // Simpan user baru
            $createdUser = User::create($userDTO);
            $createdUser->setAttribute('password_plaintext', $plainPassword);

            return ObjectResponse::success(
                __('crud.created', ['name' => 'User']),
                201,
                $createdUser
            );
        } catch (\Throwable $th) {
            return ObjectResponse::error(
                __('crud.error_create', ['name' => 'User']),
                500,
                $th->getMessage()
            );
        }
    }




    public function updateUser(array $userDTO, string $user_id)
    {
        // Temukan user berdasarkan UUID
        $getUserResponse = $this->findUserByUUID($user_id);
        if (!$getUserResponse->success) return $getUserResponse;

        try {
            // Jika ada file image yang diunggah
            if (!empty($userDTO['image'])) {
                $userDTO['image'] = $this->syncUploadFile($userDTO['image'], $getUserResponse->data->image, User::FOLDER_NAME);
            }

            // Jika ada password baru, enkripsi. Jika tidak, gunakan password lama
            $userDTO['password'] = !empty($userDTO['password'])
                ? Hash::make($userDTO['password'])
                : $getUserResponse->data->password;

            // Set status_biodata ke 1 jika berhasil update
            $userDTO['status_biodata'] = 1;

            // Lakukan update data user
            $isUpdated = $getUserResponse->data->update($userDTO);

            // Cek apakah update berhasil
            if (!$isUpdated) {
                return ObjectResponse::error(
                    __('crud.error_update', ['name' => 'User']),
                    500,
                    'Failed to update user'
                );
            }

            return ObjectResponse::success(
                __('crud.updated', ['name' => 'User']),
                200,
                $getUserResponse->data
            );
        } catch (\Throwable $th) {
            // Log error jika terjadi kesalahan
            \Log::error('User update failed: ' . $th->getMessage());
            return ObjectResponse::error(
                __('crud.error_update', ['name' => 'User']),
                500,
                $th->getMessage()
            );
        }
    }

    public function updateBerkas(array $berkasDTO, string $user_id)
    {
        $getUserResponse = $this->findUserByUUID($user_id);
        if (!$getUserResponse->success) return $getUserResponse;

        try {
            $user = $getUserResponse->data;

            // Upload new files to respective folders if available
            if (!empty($berkasDTO['img_kk'])) {
                $berkasDTO['img_kk'] = $this->syncUploadFile($berkasDTO['img_kk'], $user->img_kk, PmbStorage::DOKUMEN_KK);
            }

            if (!empty($berkasDTO['img_ktp'])) {
                $berkasDTO['img_ktp'] = $this->syncUploadFile($berkasDTO['img_ktp'], $user->img_ktp, PmbStorage::DOKUMEN_KTP);
            }

            if (!empty($berkasDTO['img_ijazah'])) {
                $berkasDTO['img_ijazah'] = $this->syncUploadFile($berkasDTO['img_ijazah'], $user->img_ijazah, PmbStorage::DOKUMEN_IJAZAH);
            }

            $updatedUser = $user->update($berkasDTO);

            return ObjectResponse::success(
                __('crud.updated', ['name' => 'Berkas']),
                200,
                $updatedUser
            );
        } catch (\Throwable $th) {
            return ObjectResponse::error(
                __('crud.error_update', ['name' => 'Berkas']),
                500,
                $th->getMessage()
            );
        }
    }

    public function updateBukti(array $berkasDTO, string $user_id)
    {
        $getUserResponse = $this->findUserByUUID($user_id);
        if (!$getUserResponse->success) return $getUserResponse;

        try {
            $user = $getUserResponse->data;

            // Upload new files to respective folders if available

            if (!empty($berkasDTO['img_bukti'])) {
                $berkasDTO['img_bukti'] = $this->syncUploadFile($berkasDTO['img_bukti'], $user->img_bukti, PmbStorage::PEMBAYARAN);
            }

            $berkasDTO['status_pemb'] = 1;
            // Update the user data
            $updatedUser = $user->update($berkasDTO);

            return ObjectResponse::success(
                __('crud.updated', ['name' => 'Berkas']),
                200,
                $updatedUser
            );
        } catch (\Throwable $th) {
            return ObjectResponse::error(
                __('crud.error_update', ['name' => 'Berkas']),
                500,
                $th->getMessage()
            );
        }
    }

    public function deleteUser(string $user_id)
    {
        $getUserResponse = $this->findUserByUUID($user_id);
        if (!$getUserResponse->success) return $getUserResponse;

        try {
            $getUserResponse->data->delete();

            return ObjectResponse::success(
                __('crud.deleted', ['name' => 'User']),
                200
            );
        } catch (\Throwable $th) {
            return ObjectResponse::error(
                __('crud.error_delete', ['name' => 'User']),
                500,
                $th->getMessage()
            );
        }
    }
}
