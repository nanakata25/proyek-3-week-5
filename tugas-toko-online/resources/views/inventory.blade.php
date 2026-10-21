@extends('layout')
@section('title', 'Stok Produk')
@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">INVENTORI TOKO</span>
        <h1>Stok produk</h1>
        <p>Akun {{ auth()->user()->username }} · Role {{ auth()->user()->role }}</p>
    </div>
    <a class="text-link" href="{{ route('products') }}">Kembali ke katalog</a>
</div>

@cannot('manage-products')
    <p>Staff dapat melihat stok. Perubahan data produk hanya dapat dilakukan admin.</p>
@endcannot

<div class="order-list">
@foreach($products as $product)
    <section class="checkout-panel" style="margin-bottom: 20px" aria-label="Produk {{ $product->id_barang }}">
        <span class="product-code">{{ $product->id_barang }}</span>
        <h2>{{ $product->nama_barang }}</h2>
        <p>{{ $product->deskripsi }}</p>
        <p>Harga: Rp{{ number_format((float) $product->harga, 0, ',', '.') }} · <strong>Stok: {{ $product->stok }}</strong></p>

        @can('manage-products')
            <details>
                <summary>Ubah data {{ $product->id_barang }}</summary>
                <form class="form" method="POST" action="{{ route('admin.products.update', $product) }}" style="margin-top: 16px">
                    @csrf
                    @method('PATCH')
                    <label for="nama-{{ $product->id_barang }}">Nama barang</label>
                    <input id="nama-{{ $product->id_barang }}" name="nama_barang" value="{{ $product->nama_barang }}" maxlength="50" required>
                    <label for="deskripsi-{{ $product->id_barang }}">Deskripsi</label>
                    <textarea id="deskripsi-{{ $product->id_barang }}" name="deskripsi" rows="2" required>{{ $product->deskripsi }}</textarea>
                    <label for="harga-{{ $product->id_barang }}">Harga</label>
                    <input id="harga-{{ $product->id_barang }}" name="harga" type="number" value="{{ $product->harga }}" min="0" max="9999999999" step="0.01" required>
                    <label for="stok-{{ $product->id_barang }}">Stok</label>
                    <input id="stok-{{ $product->id_barang }}" name="stok" type="number" value="{{ $product->stok }}" min="0" max="100000" required>
                    <button class="button" type="submit">Simpan produk {{ $product->id_barang }}</button>
                </form>
            </details>
        @endcan
    </section>
@endforeach
</div>
@endsection
