<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;
Route::get('/',[ShopController::class,'products'])->name('products');
Route::middleware('guest')->group(function(){
    Route::get('/login',[AuthController::class,'form'])->name('login');
    Route::post('/login',[AuthController::class,'login'])->middleware('throttle:10,1');
});
Route::middleware('auth')->group(function(){
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/keranjang',[ShopController::class,'cart'])->name('cart');
    Route::post('/keranjang/{id}',[ShopController::class,'add'])->name('cart.add');
    Route::patch('/keranjang/{id}',[ShopController::class,'update'])->name('cart.update');
    Route::delete('/keranjang/{id}',[ShopController::class,'remove'])->name('cart.remove');
    Route::post('/checkout',[ShopController::class,'checkout'])->name('checkout');
    Route::get('/pesanan',[ShopController::class,'orders'])->name('orders');
    Route::get('/pesanan/{id}',[ShopController::class,'order'])->name('orders.show');
});
