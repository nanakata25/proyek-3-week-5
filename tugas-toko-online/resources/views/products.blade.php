@extends('layout')
@section('title', 'Katalog Alat Tulis')
@section('content')
<section class="catalog-hero"><div><span class="eyebrow light">KOLEKSI UNTUK IDE BESARMU</span><h1>Ruang kerja rapi.<br>Hari lebih berarti.</h1><p>Alat tulis sederhana yang membuat setiap<br>catatan, sketsa, dan rencana terasa istimewa.</p><a class="hero-link" href="#koleksi">Jelajahi koleksi <span aria-hidden="true">↓</span></a></div><div class="hero-art" aria-hidden="true"><span class="hero-circle"></span><img class="hero-book" src="{{ asset('images/buku.svg') }}" alt=""><img class="hero-pen" src="{{ asset('images/pensil.svg') }}" alt=""><span class="hero-label">MADE FOR<br><strong>your next idea.</strong></span><span class="spark">✦</span></div></section>
<div class="collection-heading" id="koleksi"><div><span class="eyebrow">PILIHAN RAPI</span><h2>Perlengkapan sehari-hari</h2></div><span class="collection-count">{{ $products->count() }} barang dalam koleksi</span></div>
<div class="product-grid">
@foreach($products as $product)
    <article class="product-card {{ $product->stok === 0 ? 'sold-out' : '' }}">
        <div class="product-image"><img src="{{ asset('images/'.$product->gambar) }}" alt="{{ $product->nama_barang }}" width="240" height="168">@if($product->stok === 0)<span class="stock-badge empty">Stok habis</span>@else<span class="stock-badge">Stok {{ $product->stok }}</span>@endif</div>
        <div class="product-content"><span class="product-code">{{ $product->id_barang }}</span><h3>{{ $product->nama_barang }}</h3><p>{{ $product->deskripsi }}</p><div class="product-bottom"><strong class="price">Rp{{ number_format((float)$product->harga, 0, ',', '.') }}</strong>
            @if($product->stok === 0)<button type="button" class="add-button" disabled aria-label="{{ $product->nama_barang }} stok habis">Habis</button>
            @else
                @auth<form method="POST" action="{{ route('cart.add', $product->id_barang) }}">@csrf<button type="submit" class="add-button" aria-label="Tambah {{ $product->nama_barang }} ke keranjang">+ Tambah</button></form>@else<a class="add-button" href="{{ route('login') }}" aria-label="Login untuk membeli {{ $product->nama_barang }}">Login beli</a>@endauth
            @endif
        </div></div>
    </article>
@endforeach
</div>
@guest<div class="guest-note"><span class="note-icon" aria-hidden="true">i</span><span>Silakan jelajahi katalog. <a href="{{ route('login') }}">Login</a> untuk memasukkan barang ke keranjang dan checkout.</span></div>@endguest
@endsection
