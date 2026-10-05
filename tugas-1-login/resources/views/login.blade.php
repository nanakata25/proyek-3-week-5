@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="login-layout">
    <section class="intro-panel">
        <span class="eyebrow">SATU AKUN, RUANG BELAJARMU</span>
        <h1>Selamat datang<br>kembali.</h1>
        <p>Masuk ke akunmu dan lanjutkan perjalanan belajar hari ini.</p>
        <div class="illustration" aria-hidden="true">
            <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
            <div class="credential-card"><span class="mini-mark">r.</span><div class="card-line"></div><div class="card-line short"></div><span class="lock-mark">✓</span></div>
            <span class="floating-dot"></span><span class="floating-cross">+</span>
        </div>
        <div class="intro-note"><span class="note-dot"></span>Akses personal untuk pengalaman belajar yang nyaman.</div>
    </section>
    <section class="form-panel" aria-labelledby="login-heading">
        <span class="section-number">01 / AKSES AKUN</span>
        <h2 id="login-heading">Login</h2>
        <p class="muted">Masuk untuk membuka dashboard.</p>
        @if(session('status'))<div class="alert success" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())
            <div class="alert error" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.store') }}" class="login-form">
            @csrf
            <label for="username">Username</label>
            <input id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" maxlength="50" required autofocus @if($errors->has('username')) aria-invalid="true" @endif>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" maxlength="255" required>
            <button class="button primary" type="submit">Masuk <span aria-hidden="true">→</span></button>
        </form>
        <div class="demo-note"><span class="demo-label">AKUN PRAKTIKUM</span><p>Username <strong>budi</strong> atau <strong>siti</strong><br>Password <strong>rahasia123</strong></p></div>
    </section>
</div>
@endsection
