<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InventoryController;
Route::get('/',[ShopController::class,'products'])->name('products');
Route::get('/dashboard', fn () => view('dashboard'))->middleware('auth')->name('dashboard');
Route::get('/stok', [InventoryController::class, 'index'])->middleware(['auth', 'can:adjust-stock'])->name('inventory.index');
Route::patch('/admin/products/{product}', [InventoryController::class, 'update'])->middleware(['auth', 'can:manage-products'])->name('admin.products.update');
Route::middleware(['auth', 'can:manage-roles'])->group(function () {
    Route::get('/admin/users', [RoleController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/role', [RoleController::class, 'update'])->name('admin.users.role.update');
});
Route::middleware('guest')->group(function(){
    Route::get('/login',[AuthController::class,'form'])->name('login');
    Route::post('/login',[AuthController::class,'login'])->middleware('throttle:10,1');
});
Route::middleware('auth')->group(function(){
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/keranjang',[ShopController::class,'cart'])->middleware('can:use-cart')->name('cart');
    Route::post('/keranjang/{id}',[ShopController::class,'add'])->middleware('can:use-cart')->name('cart.add');
    Route::patch('/keranjang/{id}',[ShopController::class,'update'])->middleware('can:use-cart')->name('cart.update');
    Route::delete('/keranjang/{id}',[ShopController::class,'remove'])->middleware('can:use-cart')->name('cart.remove');
    Route::post('/checkout',[ShopController::class,'checkout'])->middleware('can:use-cart')->name('checkout');
    Route::get('/pesanan',[ShopController::class,'orders'])->name('orders');
    Route::get('/pesanan/{id}',[ShopController::class,'order'])->name('orders.show');
});

Route::get('/stateless', [CartController::class, 'index'])
    ->middleware(['auth', 'can:use-cart'])
    ->name('stateless.index');

Route::post('/cart/add', [CartController::class, 'add'])
    ->middleware(['auth', 'can:use-cart'])
    ->name('stateless.cart.add');

Route::post('/cart/update', [CartController::class, 'update'])
    ->middleware(['auth', 'can:use-cart'])
    ->name('stateless.cart.update');
