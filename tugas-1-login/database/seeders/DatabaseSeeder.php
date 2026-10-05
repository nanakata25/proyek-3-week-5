<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Model cast 'hashed' hashes these demonstration passwords before storage.
        foreach ([['budi', 'Budi Santoso'], ['siti', 'Siti Aminah']] as [$username, $nama]) {
            User::updateOrCreate(['username' => $username], [
                'nama_lengkap' => $nama,
                'password' => 'rahasia123',
            ]);
        }
    }
}
