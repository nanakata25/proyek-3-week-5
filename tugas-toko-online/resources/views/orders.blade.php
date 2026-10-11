@extends('layout')
@section('title', 'Riwayat Pesanan')
@section('content')
<div class="page-heading"><div><span class="eyebrow">CATATAN BELANJAMU</span><h1>Riwayat pesanan</h1><p>Semua pesananmu, tersimpan dalam satu tempat.</p></div><a class="text-link" href="{{ route('products') }}">Kembali ke katalog →</a></div>
@if($orders->total() === 0)<section class="empty-state"><span class="empty-symbol" aria-hidden="true">≡</span><h2>Belum ada pesanan</h2><p>Pesanan pertama akan muncul di sini setelah kamu checkout.</p><a class="button" href="{{ route('products') }}">Mulai belanja <span aria-hidden="true">→</span></a></section>
@elseif($orders->isEmpty())
<section class="empty-state"><span class="empty-symbol" aria-hidden="true">≡</span><h2>Halaman pesanan tidak ditemukan</h2><p>Halaman ini tidak memuat pesanan. Buka halaman pertama untuk melihat riwayatmu.</p><a class="button" href="{{ route('orders') }}">Ke halaman pertama <span aria-hidden="true">→</span></a></section>
@else
<div class="orders-list">@foreach($orders as $order)<article class="order-card"><div><span class="eyebrow">{{ \Carbon\Carbon::parse($order->tanggal_order)->format('d/m/Y · H:i') }} WIB</span><h2>{{ $order->id_order }}</h2><span class="status-chip">✓ Pesanan tercatat</span></div><div class="order-value"><span>Total pembayaran</span><strong>Rp{{ number_format((float)$order->total_harga, 0, ',', '.') }}</strong></div><a class="button outline" href="{{ route('orders.show', $order->id_order) }}">Lihat detail <span aria-hidden="true">→</span></a></article>@endforeach</div>
@if($orders->hasPages())
<nav class="collection-heading order-pagination" aria-label="Navigasi riwayat pesanan">
    <span class="text-link" aria-current="page">Halaman {{ $orders->currentPage() }} dari {{ $orders->lastPage() }}</span>
    <div class="account-nav">
        @if($orders->previousPageUrl())<a class="button outline button-small" href="{{ $orders->previousPageUrl() }}" rel="prev"><span aria-hidden="true">←</span> Sebelumnya</a>@endif
        @if($orders->hasMorePages())<a class="button outline button-small" href="{{ $orders->nextPageUrl() }}" rel="next">Berikutnya <span aria-hidden="true">→</span></a>@endif
    </div>
</nav>
@endif
@endif
@endsection
