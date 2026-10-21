<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class RoleDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Seeder role demo hanya boleh dijalankan secara lokal.');
        }

        foreach ([
            ['M5ADMIN', 'admin_modul5', 'Admin Modul 5', 'admin'],
            ['M5ADMIN2', 'admin_lain5', 'Admin Lain Modul 5', 'admin'],
            ['M5CUSTOMER', 'customer_modul5', 'Customer Modul 5', 'customer'],
            ['M5STAFF', 'staff_modul5', 'Staff Modul 5', 'staff'],
        ] as [$id, $username, $name, $role]) {
            if (User::whereKey($id)->exists()) continue;
            $user = new User([
                'id_user' => $id, 'username' => $username,
                'nama_lengkap' => $name, 'email' => $username.'@example.test',
                'password' => 'BelajarRole5!', 'no_hp' => '0000000000',
                'alamat' => 'Data dummy praktikum lokal',
            ]);
            $user->role = $role;
            $user->save();
        }
    }
}
