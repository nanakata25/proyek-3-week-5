@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="dashboard-layout">
    <div class="dashboard-heading"><div><span class="section-number">RUANG PERSONAL</span><h1>Dashboard</h1></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="button secondary" type="submit">Logout <span aria-hidden="true">↗</span></button></form></div>
    @if(session('status'))<div class="alert success" role="status">{{ session('status') }}</div>@endif
    <section class="welcome-panel">
        <div><span class="eyebrow">SENANG BERTEMU LAGI</span><h2>Selamat datang,<br>{{ $user->nama_lengkap }}!</h2><p>Halaman ini hanya bisa dibuka setelah login.<br>Akunmu sudah terhubung dan siap digunakan.</p><span class="active-badge"><span></span>Sesi aktif</span></div>
        <div class="welcome-emblem" aria-hidden="true"><span>{{ mb_substr($user->nama_lengkap, 0, 1) }}</span><div>✓</div></div>
    </section>
    <div class="dashboard-grid">
        <section class="detail-card"><span class="section-number">PROFIL AKUN</span><h3>Identitas pengguna</h3><dl><div><dt>Nama lengkap</dt><dd>{{ $user->nama_lengkap }}</dd></div><div><dt>Username</dt><dd>{{ $user->username }}</dd></div></dl></section>
        <section class="detail-card next-card"><span class="section-number">SELESAI BELAJAR?</span><h3>Jaga akses akunmu.</h3><p>Gunakan tombol Logout setelah selesai, terutama ketika menggunakan perangkat bersama.</p><span class="small-rule"></span></section>
    </div>
</div>
@endsection
