<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Contracts\View\View;

class KeranjangController extends Controller
{
    public function katalog(): View
    {
        return view('katalog', ['barang' => Barang::orderBy('id')->get()]);
    }

    public function keranjang(): View
    {
        return view('keranjang', ['barang' => Barang::orderBy('id')->get()]);
    }
}
