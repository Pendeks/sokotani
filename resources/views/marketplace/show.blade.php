@extends('layouts.app')

@section('title', $product->name.' — SokoTani')

@section('content')
<section class="page-section product-detail-section">
    <a class="back-link" href="{{ route('home') }}">← Kembali ke katalog</a>
    <div class="product-detail">
        <div class="detail-image-wrap">
            @if ($product->image_path)
                <img class="detail-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">
            @else
                <div class="detail-image listing-placeholder" aria-hidden="true">🌾</div>
            @endif
        </div>
        <div class="detail-copy">
            <span class="tag">{{ $product->category }}</span>
            <h1>{{ $product->name }}</h1>
            <p class="listing-location">📍 {{ $product->location }} · Petani <a class="farmer-name-link" href="{{ route('farmers.show', $product->farmer) }}">{{ $product->farmer->name }}</a> @if($product->farmer->is_verified)<span class="verified-inline" title="Petani terverifikasi">✓ Terverifikasi</span>@endif</p>
            <p class="detail-price">Rp{{ number_format($product->asking_price_per_unit, 0, ',', '.') }} <span>/ {{ $product->unit }}</span></p>
            <p class="negotiable-note">Harga acuan — silakan ajukan harga yang Anda tawarkan.</p>
            @auth
                @if (auth()->user()->role === 'buyer' && $product->farmer_id !== auth()->id() && $product->is_active && (float) $product->available_quantity > 0)
                    <a class="button button-primary detail-offer-cta" href="#offer-form"><span aria-hidden="true">↗</span> Ajukan penawaran grosir</a>
                @elseif (auth()->user()->role === 'farmer' && $product->farmer_id !== auth()->id())
                    <div class="role-hint"><strong>Untuk mengajukan harga, gunakan akun pembeli.</strong><span>Anda sedang masuk sebagai petani. Buka menu akun di kanan atas, pilih Keluar, lalu masuk dengan akun pembeli.</span></div>
                @endif
            @else
                <div class="role-hint"><strong>Masuk sebagai pembeli untuk mengajukan harga.</strong><span>Tentukan jumlah grosir dan harga per {{ $product->unit }} yang Anda tawarkan.</span><a href="{{ route('login') }}">Masuk sebagai pembeli →</a></div>
            @endauth
            <p class="detail-description">{{ $product->description }}</p>
            <dl class="detail-facts">
                <div><dt>Stok tersedia</dt><dd>{{ rtrim(rtrim($product->available_quantity, '0'), '.') }} {{ $product->unit }}</dd></div>
                <div><dt>Minimum pembelian</dt><dd>{{ rtrim(rtrim($product->minimum_quantity, '0'), '.') }} {{ $product->unit }}</dd></div>
                <div><dt>Perkiraan panen</dt><dd>{{ $product->harvest_date?->translatedFormat('d F Y') ?? 'Siap sesuai kesepakatan' }}</dd></div>
            </dl>

            @auth
                @if (auth()->user()->role === 'buyer' && $product->farmer_id !== auth()->id() && $product->is_active && (float) $product->available_quantity > 0)
                    <div class="form-card offer-card" id="offer-form" tabindex="-1">
                        <h2>Ajukan penawaran grosir</h2>
                        <p class="muted">Negosiasi berlangsung di SokoTani. Ajukan harga per {{ $product->unit }}; petani dapat menerima, menolak, atau membalas dengan harga lain. Anda akan melihat semua tanggapan di halaman Penawaran &amp; pesanan.</p>
                        <ol class="negotiation-steps"><li>Ajukan jumlah dan harga</li><li>Petani menanggapi atau menawar balik</li><li>Sepakati harga sebelum pesanan dikonfirmasi</li></ol>
                        @if ($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                        <form method="POST" action="{{ route('offers.store', $product) }}" class="stack-form">
                            @csrf
                            <label>Jumlah ({{ $product->unit }})
                                <input type="number" name="quantity" min="{{ $product->minimum_quantity }}" max="{{ $product->available_quantity }}" step="0.01" value="{{ old('quantity', $product->minimum_quantity) }}" required>
                                <small>Minimum {{ rtrim(rtrim($product->minimum_quantity, '0'), '.') }} · tersedia {{ rtrim(rtrim($product->available_quantity, '0'), '.') }}</small>
                            </label>
                            <label>Tawaran harga per {{ $product->unit }} (Rp)
                                <input type="number" name="unit_price" min="1" max="1000000000000" step="1" value="{{ old('unit_price', $product->asking_price_per_unit) }}" required>
                            </label>
                            <label>Pengambilan / pengiriman
                                <select name="fulfillment_method" required><option value="pickup">Ambil di petani</option><option value="delivery" @selected(old('fulfillment_method') === 'delivery')>Pengiriman oleh petani (atur langsung)</option></select>
                            </label>
                            <label>Catatan lokasi atau kesepakatan
                                <textarea name="fulfillment_notes" rows="2" maxlength="1000" placeholder="Contoh: alamat tujuan atau waktu pengambilan">{{ old('fulfillment_notes') }}</textarea>
                            </label>
                            <label>Pesan untuk petani (opsional)
                                <textarea name="message" rows="2" maxlength="1000" placeholder="Sampaikan kebutuhan Anda">{{ old('message') }}</textarea>
                            </label>
                            <button class="button button-primary" type="submit">Kirim penawaran</button>
                        </form>
                    </div>
                @elseif (auth()->user()->role === 'farmer' && $product->farmer_id === auth()->id())
                    <a class="button button-primary" href="{{ route('farmer.listings.edit', $product) }}">Kelola produk</a>
                @endif
            @else
                <div class="form-card offer-card"><h2>Tertarik membeli grosir?</h2><p class="muted">Masuk atau daftar sebagai pembeli untuk mengajukan harga kepada petani.</p><a class="button button-primary" href="{{ route('login') }}">Masuk untuk menawar</a></div>
            @endauth
        </div>
    </div>
</section>
@endsection
