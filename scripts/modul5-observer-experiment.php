<?php
require __DIR__.'/../tugas-toko-online/vendor/autoload.php';
$app=require __DIR__.'/../tugas-toko-online/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || basename(config('database.connections.sqlite.database')) !== 'modul5-demo.sqlite') {
    throw new RuntimeException('Eksperimen hanya untuk database dummy Modul 5.');
}
$user=App\Models\User::findOrFail('M5CUSTOMER');
if (($argv[1] ?? '') === 'bulk') {
    App\Models\User::whereKey($user->getKey())->update(['role'=>'customer']);
} elseif (($argv[1] ?? '') === 'restore') {
    $user->role='staff';
    $user->save();
} else {
    throw new InvalidArgumentException('Gunakan bulk atau restore.');
}
echo json_encode(['id'=>$user->getKey(),'role'=>$user->fresh()->role]);
