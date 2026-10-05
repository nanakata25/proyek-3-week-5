<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102f3d">
    <title>@yield('title', 'Akses Akun') · Ruang Belajar</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ url('/') }}"><span class="brand-symbol" aria-hidden="true">r.</span><span>ruang<span class="brand-light">belajar</span></span></a>
        <span class="course-pill">Praktikum Web <span>04</span></span>
    </header>
    <main>@yield('content')</main>
    <footer class="site-footer"><span>Dyas Nakata Hilwan · 251511009</span><span>Tugas 1 · Autentikasi pengguna</span></footer>
</body>
</html>
