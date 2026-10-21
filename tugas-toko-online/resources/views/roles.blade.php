@extends('layout')
@section('title', 'Manajemen Role')
@section('content')
<span class="eyebrow">ADMINISTRASI PENGGUNA</span>
<h1>Manajemen Role</h1>
<p>Ubah akses customer dan staff. Akun admin ditetapkan melalui seeder tepercaya.</p>
<p>Login sebagai <strong>{{ auth()->user()->username }}</strong> · role <strong>{{ auth()->user()->role }}</strong></p>
<div style="overflow-x:auto;background:white;padding:24px;border-radius:12px">
<table style="width:100%;border-collapse:collapse;text-align:left">
    <thead><tr><th>ID</th><th>Nama</th><th>Username</th><th>Role saat ini</th><th>Ubah role</th></tr></thead>
    <tbody>
    @foreach($users as $user)
        <tr style="border-bottom:1px solid #ddd" data-user-id="{{ $user->getKey() }}">
            <td style="padding:16px 8px">{{ $user->getKey() }}</td><td>{{ $user->nama_lengkap }}</td>
            <td>{{ $user->username }}</td><td><strong>{{ $user->role }}</strong></td>
            <td>
            @if($user->role === 'admin')
                <span>Akun admin dilindungi</span>
            @else
                <form method="POST" action="{{ route('admin.users.role.update', $user) }}" style="display:flex;gap:12px;align-items:center">
                    @csrf @method('PATCH')
                    <select name="role" aria-label="Role {{ $user->username }}" style="padding:10px">
                        <option value="customer" @selected($user->role === 'customer')>Customer</option>
                        <option value="staff" @selected($user->role === 'staff')>Staff</option>
                    </select>
                    <button class="button button-small" type="submit">Simpan</button>
                </form>
            @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
@endsection
