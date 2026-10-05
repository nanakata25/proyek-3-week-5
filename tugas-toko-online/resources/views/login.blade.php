@extends('layout')
@section('title', 'Login')
@section('content')
<section class="login-layout">
    <div class="login-intro"><span class="eyebrow light">SEMUA BERAWAL DARI IDE</span><h1>Temani setiap<br>langkah kreatifmu.</h1><p>Masuk untuk menyimpan keranjang,<br>berbelanja, dan melihat pesananmu.</p><div class="login-art" aria-hidden="true"><img src="{{ asset('images/buku.svg') }}" alt=""><span>Catat.<br>Ciptakan.<br>Mulai lagi.</span></div><span class="login-note">Alat tulis pilihan untuk hari yang produktif.</span></div>
    <div class="login-form-panel"><span class="eyebrow">SELAMAT DATANG KEMBALI</span><h2>Login ke akunmu</h2><p class="muted">Satu langkah lagi menuju perlengkapan favorit.</p><form method="POST" action="{{ route('login') }}" class="form">@csrf<label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" maxlength="50" autofocus required><label for="password">Password</label><input id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" maxlength="255" required><button type="submit" class="button">Masuk <span aria-hidden="true">→</span></button></form><div class="demo-note"><span class="eyebrow">AKUN PRAKTIKUM</span><p><strong>budi</strong> atau <strong>siti</strong> · Password <strong>rahasia123</strong></p></div><a class="text-link" href="{{ route('products') }}">← Jelajahi katalog dahulu</a></div>
</section>
@endsection
