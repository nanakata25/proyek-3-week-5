<?php

use App\Http\Controllers\KeranjangController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KeranjangController::class, 'katalog'])->name('katalog');
Route::get('/keranjang', [KeranjangController::class, 'keranjang'])->name('keranjang');
