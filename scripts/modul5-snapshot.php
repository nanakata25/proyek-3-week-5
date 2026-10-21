<?php
require __DIR__.'/../tugas-toko-online/vendor/autoload.php';
$app=require __DIR__.'/../tugas-toko-online/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode([
    'users'=>App\Models\User::select('id_user','username','nama_lengkap','role')->orderBy('id_user')->get()->toArray(),
    'products'=>App\Models\Product::select('id_barang','nama_barang','stok')->orderBy('id_barang')->get()->toArray(),
    'driver'=>Illuminate\Support\Facades\DB::connection()->getDriverName(),
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
