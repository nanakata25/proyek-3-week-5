<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#123a40">
    <title>@yield('title') · Ruang Tulis</title>
    <link rel="stylesheet" href="{{ asset('css/toko.css') }}">
    <script id="catalog-data" type="application/json">{!! json_encode($barang->map(fn ($item) => $item->only(['id', 'nama', 'harga', 'stok']))->values(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="module" src="{{ asset('js/keranjang.js') }}"></script>
</head>
<body data-page="@yield('page')">
    <div class="topline">ALAT TULIS UNTUK IDE-IDE BESAR <span>Praktikum Modul 4 · Tugas 2</span></div>
    <header class="header wrap">
        <a class="brand" href="{{ route('katalog') }}" aria-label="Ruang Tulis, beranda">
            <span class="brand-icon" aria-hidden="true">rt.</span><span>ruang tulis<small>Mulai dari selembar ide.</small></span>
        </a>
        <nav aria-label="Navigasi utama">
            <a href="{{ route('katalog') }}" @class(['nav-link', 'active' => request()->routeIs('katalog')])>Daftar barang</a>
            <a href="{{ route('keranjang') }}" class="cart-link" @if(request()->routeIs('keranjang')) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 4h2l2 11h12l2-8H6M9 20h.01M18 20h.01" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                Keranjang <span class="cart-count" data-cart-count>0</span>
            </a>
        </nav>
    </header>
    <main class="wrap">
        <noscript><div class="notice">Aktifkan JavaScript untuk menggunakan keranjang Local Storage.</div></noscript>
        <div id="storage-notice" class="notice" role="status" hidden></div>
        @yield('content')
    </main>
    <footer class="footer wrap"><span><strong>ruang tulis</strong> · Toko alat tulis sederhana</span><span>Tanpa login · Tersimpan di browser ini</span></footer>
    <div id="toast" class="toast" role="status" aria-live="polite" hidden></div>
</body>
</html>
