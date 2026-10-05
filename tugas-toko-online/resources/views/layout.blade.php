<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#123a40">
    <title>@yield('title', 'Katalog') · Rapi Stationery</title>
    <link rel="stylesheet" href="{{ asset('css/toko.css') }}">
</head>
<body>
    <header class="header">
        <a class="brand" href="{{ route('products') }}"><span class="brand-mark">r.</span><span>rapi<span class="brand-caption">STATIONERY</span></span></a>
        <nav aria-label="Navigasi utama">
            <a class="nav-link {{ request()->routeIs('products') ? 'active' : '' }}" href="{{ route('products') }}">Katalog</a>
            @auth
                @php($cartCount = \Illuminate\Support\Facades\DB::table('cart_items')->where('id_user', auth()->id())->sum('jumlah'))
                <a class="nav-link {{ request()->routeIs('cart') ? 'active' : '' }}" href="{{ route('cart') }}">Keranjang <span class="count">{{ $cartCount }}</span></a>
                <a class="nav-link {{ request()->routeIs('orders*') ? 'active' : '' }}" href="{{ route('orders') }}">Pesanan</a>
            @endauth
        </nav>
        <div class="account-nav">
            @auth
                <span class="user-chip"><span class="avatar">{{ mb_substr(auth()->user()->nama_lengkap, 0, 1) }}</span>{{ auth()->user()->username }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Logout</button></form>
            @else
                <a class="button button-small" href="{{ route('login') }}">Login <span aria-hidden="true">↗</span></a>
            @endauth
        </div>
    </header>
    <main class="main">
        @if(session('success'))<div class="alert success" role="status"><span aria-hidden="true">✓</span> {{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert error" role="alert">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
    <footer class="footer"><span><strong>rapi.</strong> Kebutuhan kecil, ide besar.</span><span>Dyas Nakata Hilwan · 251511009 · Modul 4</span></footer>
</body>
</html>
