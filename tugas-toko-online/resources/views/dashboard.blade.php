@extends('layout')
@section('title', 'Dashboard')
@section('content')
<span class="eyebrow">AKUN SAYA</span><h1>Dashboard</h1>
<p>Selamat datang, {{ auth()->user()->nama_lengkap }}.</p>
<p>Username <strong>{{ auth()->user()->username }}</strong> · role <strong>{{ auth()->user()->role }}</strong></p>
<p>Menu yang tersedia mengikuti izin akun Anda pada setiap request.</p>
@can('manage-roles')<a class="button" href="{{ route('admin.users.index') }}">Manajemen Role</a>@endcan
@can('use-cart')<a class="button" href="{{ route('stateless.index') }}">Keranjang Stateless</a>@endcan
@endsection
