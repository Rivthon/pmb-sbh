<?php

namespace Database\Seeders\MasterData;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Super Admin',
            'code' => (new User)->generateUniqueCode(USER::ADMIN_PREFIX_CODE),
            'email' => 'admin@stockflow.fun',
            'phone' => '081122334455',
            'address' => 'Bogor, Indonesia',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ADMIN_ROLE,
        ]);
    }
}
