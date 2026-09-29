@extends('layouts.app')

@section('title', 'Notifikasi — SokoTani')

@section('content')
<section class="page-section">
    <div class="section-heading"><div><p class="eyebrow">PEMBARUAN TRANSAKSI</p><h1>Notifikasi</h1><p class="muted">Pembaruan penawaran, negosiasi, dan pesanan Anda.</p></div>@if(auth()->user()->unreadNotifications()->exists())<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="button button-outline" type="submit">Tandai semua dibaca</button></form>@endif</div>
    @if ($notifications->isNotEmpty())
        <div class="notification-list">
            @foreach ($notifications as $notification)
                <form class="notification-item{{ $notification->read_at ? '' : ' is-unread' }}" method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <span class="notification-indicator" aria-hidden="true"></span>
                    <div class="notification-copy"><strong>{{ $notification->data['title'] ?? 'Pembaruan SokoTani' }}</strong><span>{{ $notification->data['message'] ?? '' }}</span><small>{{ $notification->created_at->diffForHumans() }} · {{ $notification->data['product_name'] ?? '' }}</small></div>
                    <button type="submit" class="notification-open">{{ $notification->read_at ? 'Buka' : 'Lihat' }} →</button>
                </form>
            @endforeach
        </div>
        <div class="pagination-wrap">{{ $notifications->links() }}</div>
    @else
        <div class="empty-state"><span aria-hidden="true">🔔</span><h2>Belum ada notifikasi</h2><p>Pembaruan aktivitas penawaran Anda akan muncul di sini.</p></div>
    @endif
</section>
@endsection
