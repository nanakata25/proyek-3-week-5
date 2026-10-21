<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        return view('inventory', ['products' => Product::orderBy('id_barang')->get()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'nama_barang' => ['required', 'string', 'max:50'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'stok' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $product->nama_barang = $data['nama_barang'];
        $product->deskripsi = $data['deskripsi'];
        $product->harga = $data['harga'];
        $product->stok = $data['stok'];
        $product->save();

        return redirect()->route('inventory.index')->with('success', 'Data produk diperbarui.');
    }
}
