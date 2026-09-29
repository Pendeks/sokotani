@extends('layouts.app')

@section('title', 'Verifikasi petani — SokoTani')

@section('content')
<section class="page-section">
    <div class="section-heading"><div><p class="eyebrow">ADMIN SOKOTANI</p><h1>Verifikasi petani</h1><p class="muted">Tinjau profil publik dan informasi usaha tani sebelum memberi tanda terverifikasi.</p></div></div>
    <div class="verification-list">
        @forelse ($farmers as $farmer)
            <article class="verification-card">
                <div class="verification-card-info"><span class="farmer-profile-avatar small-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($farmer->name, 0, 1)) }}</span><div><h2>{{ $farmer->name }} @if($farmer->is_verified)<span class="verified-badge">✓ Terverifikasi</span>@else<span class="unverified-badge">Belum terverifikasi</span>@endif</h2><p>{{ $farmer->email }} · {{ $farmer->farm_location ?: 'Lokasi belum diisi' }}</p><p>{{ $farmer->products_count }} produk · <a href="{{ route('farmers.show', $farmer) }}" target="_blank" rel="noopener">Lihat profil publik</a></p><p class="admin-farmer-bio">{{ $farmer->farm_description ?: 'Belum ada deskripsi kebun.' }}</p></div></div>
                <form method="POST" action="{{ route('admin.farmers.verification', $farmer) }}">@csrf @method('PATCH')<input type="hidden" name="is_verified" value="{{ $farmer->is_verified ? '0' : '1' }}"><button class="button {{ $farmer->is_verified ? 'button-danger-outline' : 'button-primary' }}" type="submit">{{ $farmer->is_verified ? 'Cabut verifikasi' : 'Verifikasi petani' }}</button></form>
            </article>
        @empty
            <div class="empty-state"><h2>Belum ada akun petani</h2></div>
        @endforelse
    </div>
    <div class="pagination-wrap">{{ $farmers->links() }}</div>
</section>
@endsection
