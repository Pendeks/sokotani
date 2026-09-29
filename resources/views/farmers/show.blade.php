@extends('layouts.app')

@section('title', $farmer->name.' — Profil petani SokoTani')

@section('content')
<section class="page-section farmer-profile-section">
    <a class="back-link" href="{{ route('home') }}">← Kembali ke katalog</a>
    <div class="farmer-profile-card">
        <div class="farmer-profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($farmer->name, 0, 1)) }}</div>
        <div class="farmer-profile-heading">
            <p class="eyebrow">PROFIL PETANI SOKOTANI</p>
            <h1>{{ $farmer->name }} @if($farmer->is_verified)<span class="verified-badge" title="Petani telah diverifikasi">✓ Terverifikasi</span>@endif</h1>
            <p class="farmer-profile-location">📍 {{ $farmer->farm_location ?: 'Lokasi belum ditambahkan' }}</p>
        </div>
        <div class="farmer-profile-bio"><h2>Tentang petani</h2><p>{{ $farmer->farm_description ?: 'Petani ini belum menambahkan deskripsi profil.' }}</p></div>
    </div>

    <div class="section-heading farmer-products-heading"><div><p class="eyebrow">HASIL TANI</p><h2>Produk dari {{ $farmer->name }}</h2></div></div>
    @if ($farmer->products()->where('is_active', true)->where('available_quantity', '>', 0)->exists())
        <div class="listing-grid">
            @foreach ($farmer->products()->where('is_active', true)->where('available_quantity', '>', 0)->latest()->get() as $product)
                <article class="listing-card"><a href="{{ route('products.show', $product) }}" class="listing-image-link" aria-label="Lihat {{ $product->name }}"><div class="listing-image listing-placeholder" aria-hidden="true">🌾</div></a><div class="listing-body"><span class="tag">{{ $product->category }}</span><h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3><p class="listing-location">📍 {{ $product->location }}</p><p class="listing-price">Rp{{ number_format($product->asking_price_per_unit, 0, ',', '.') }} <span>/ {{ $product->unit }}</span></p><div class="listing-meta"><span>Minimum {{ rtrim(rtrim($product->minimum_quantity, '0'), '.') }} {{ $product->unit }}</span><span>Stok {{ rtrim(rtrim($product->available_quantity, '0'), '.') }} {{ $product->unit }}</span></div><a class="button button-outline button-full" href="{{ route('products.show', $product) }}">Lihat &amp; ajukan harga</a></div></article>
            @endforeach
        </div>
    @else
        <div class="empty-state"><h2>Belum ada produk tersedia</h2><p>Produk petani ini akan tampil di sini saat tersedia.</p></div>
    @endif
</section>
@endsection
