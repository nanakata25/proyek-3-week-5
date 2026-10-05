@extends('layout')
@section('title', 'Daftar Barang')
@section('page', 'katalog')
@section('content')
    <section class="hero">
        <div><span class="eyebrow">KEBUTUHAN KECIL, KARYA BESAR</span><h1>Bekal untuk<br><em>setiap ide.</em></h1><p>Temukan teman menulis, mencatat, dan berkarya.<br>Pilih kebutuhanmu, simpan di keranjang.</p><a class="text-link" href="#produk">Jelajahi koleksi <span aria-hidden="true">↗</span></a></div>
        <div class="hero-art" aria-hidden="true"><span class="orbit orbit-one"></span><span class="orbit orbit-two"></span><div class="hero-notebook"><span>IDE<br>HARI INI.</span><small>RUANG TULIS / 01</small></div><div class="hero-pencil"></div><span class="hero-note">Satu halaman.<br>Banyak kemungkinan.</span></div>
    </section>
    <section id="produk" class="catalog-section">
        <div class="section-heading"><div><span class="eyebrow">KOLEKSI PILIHAN</span><h2>Daftar barang</h2></div><p>{{ $barang->count() }} produk · Harga bersahabat</p></div>
        <div class="product-grid">
            @foreach($barang as $item)
                <article class="product-card">
                    <div class="art-bg art-bg-{{ $item->id }}"><span class="product-index">0{{ $loop->iteration }}</span><x-product-art :id="$item->id" /></div>
                    <div class="product-info"><div class="product-title"><h3>{{ $item->nama }}</h3><span class="stock">Stok {{ $item->stok }}</span></div><p class="price">Rp {{ number_format($item->harga, 0, ',', '.') }}</p><button class="add-button" data-add="{{ $item->id }}" @disabled($item->stok === 0)><span>Masukkan ke keranjang</span><span aria-hidden="true">+</span></button></div>
                </article>
            @endforeach
        </div>
    </section>
    <div class="assurance"><span class="assurance-icon" aria-hidden="true">✓</span><p><strong>Belanja lebih praktis, tanpa akun.</strong><br>Keranjang tetap tersimpan saat halaman dimuat ulang.</p><a href="{{ route('keranjang') }}">Lihat keranjang <span aria-hidden="true">→</span></a></div>
@endsection
