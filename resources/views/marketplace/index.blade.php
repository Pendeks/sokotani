@extends('layouts.app')

@section('title', 'Pasar hasil tani grosir — SokoTani')

@section('content')
<section class="market-hero">
    <div class="market-hero-inner">
        <div>
            <p class="eyebrow">PASAR TANI LOKAL · PEMBELIAN GROSIR</p>
            <h1>Panen lokal, transaksi langsung.</h1>
            <p>Temukan hasil tani dari petani sekitar. Ajukan harga terbaik untuk kebutuhan grosir Anda.</p>
            <a class="button button-light" href="#katalog">Jelajahi hasil tani <span aria-hidden="true">↓</span></a>
        </div>
        @if ($featuredProducts->isNotEmpty())
            <section class="featured-carousel" aria-label="Hasil tani pilihan" data-featured-carousel aria-roledescription="carousel">
                <div class="featured-carousel-top"><div><span class="featured-kicker">ETALASE PETANI</span><h2>Hasil panen pilihan</h2></div><span class="featured-count"><span data-carousel-current>1</span> / {{ $featuredProducts->count() }}</span></div>
                <div class="featured-slides" aria-live="polite">
                    @foreach ($featuredProducts as $index => $featuredProduct)
                        <article class="featured-slide{{ $index === 0 ? ' is-active' : '' }}" data-carousel-slide aria-roledescription="slide" aria-label="{{ $index + 1 }} dari {{ $featuredProducts->count() }}" @if($index !== 0) aria-hidden="true" @endif>
                            <a class="featured-image-link" href="{{ route('products.show', $featuredProduct) }}" tabindex="{{ $index === 0 ? '0' : '-1' }}">
                                <img class="featured-image" src="{{ $featuredProduct->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($featuredProduct->image_path) : asset('images/hero.png') }}" alt="{{ $featuredProduct->image_path ? $featuredProduct->name : 'Petani lokal dengan hasil panen segar' }}">
                            </a>
                            <div class="featured-product-info">
                                <div class="featured-product-copy"><span class="featured-category">{{ $featuredProduct->category }} · {{ $featuredProduct->location }}</span><h3><a href="{{ route('products.show', $featuredProduct) }}">{{ $featuredProduct->name }}</a></h3><p>oleh <a href="{{ route('farmers.show', $featuredProduct->farmer) }}">{{ $featuredProduct->farmer->name }}</a> @if($featuredProduct->farmer->is_verified)<span class="verified-inline" title="Petani terverifikasi">✓</span>@endif</p></div>
                                <div class="featured-price"><strong>Rp{{ number_format($featuredProduct->asking_price_per_unit, 0, ',', '.') }}</strong><span>/ {{ $featuredProduct->unit }}</span></div>
                                <a class="button button-featured" href="{{ route('products.show', $featuredProduct) }}">Lihat &amp; tawar <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="featured-carousel-controls">
                    <p><strong>Harga bisa dinegosiasikan.</strong><span>Ajukan jumlah dan harga langsung ke petani.</span></p>
                    <div class="featured-carousel-buttons">
                        <button type="button" class="carousel-arrow" data-carousel-prev aria-label="Produk sebelumnya">←</button>
                        <div class="carousel-dots" aria-label="Pilih produk">
                            @foreach ($featuredProducts as $index => $featuredProduct)
                                <button type="button" class="carousel-dot{{ $index === 0 ? ' is-active' : '' }}" data-carousel-dot="{{ $index }}" aria-label="Lihat {{ $featuredProduct->name }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                        <button type="button" class="carousel-arrow" data-carousel-next aria-label="Produk berikutnya">→</button>
                    </div>
                </div>
            </section>
        @else
            <div class="featured-empty-card"><img src="{{ asset('images/hero.png') }}" alt="Petani lokal membawa hasil panen"><div><span class="featured-kicker">PASAR TANI LOKAL</span><h2>Panen Anda layak dikenal.</h2><p>Pasang produk, lalu biarkan pembeli grosir mengajukan harga.</p><a href="{{ route('register') }}" class="button button-featured">Mulai berjualan →</a></div></div>
        @endif
    </div>
</section>

<section class="page-section catalog-section" id="katalog">
    <div class="section-heading">
        <div><p class="eyebrow">KATALOG PETANI</p><h2>Hasil tani tersedia</h2><p class="muted">Harga yang tertera adalah harga acuan per unit dan dapat dinegosiasikan.</p></div>
        @auth
            @if (auth()->user()->role === 'farmer')
                <a class="button button-primary" href="{{ route('farmer.listings.create') }}">+ Pasang produk</a>
            @endif
        @else
            <a class="button button-primary" href="{{ route('register') }}">Mulai berjualan</a>
        @endauth
    </div>

    <form method="GET" action="{{ route('home') }}" class="filter-bar">
        <label class="sr-only" for="product-search">Cari produk atau lokasi</label>
        <input id="product-search" name="q" value="{{ request('q') }}" placeholder="Cari produk, kategori, atau lokasi…">
        <select name="category" aria-label="Kategori">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <input name="location" value="{{ request('location') }}" placeholder="Lokasi">
        <button class="button button-dark" type="submit">Cari</button>
        @if (request()->query())<a class="clear-filter" href="{{ route('home') }}">Hapus filter</a>@endif
    </form>

    @if ($products->count())
        <div class="listing-grid">
            @foreach ($products as $product)
                <article class="listing-card">
                    <a href="{{ route('products.show', $product) }}" class="listing-image-link" aria-label="Lihat {{ $product->name }}">
                        @if ($product->image_path)
                            <img class="listing-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">
                        @else
                            <div class="listing-image listing-placeholder" aria-hidden="true">🌾</div>
                        @endif
                    </a>
                    <div class="listing-body">
                        <span class="tag">{{ $product->category }}</span>
                        <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
                        <p class="listing-location">📍 {{ $product->location }} · <a class="farmer-name-link" href="{{ route('farmers.show', $product->farmer) }}">{{ $product->farmer->name }}</a> @if($product->farmer->is_verified)<span class="verified-inline" title="Petani terverifikasi">✓ Terverifikasi</span>@endif</p>
                        <p class="listing-price">Rp{{ number_format($product->asking_price_per_unit, 0, ',', '.') }} <span>/ {{ $product->unit }}</span></p>
                        <div class="listing-meta"><span>Min. {{ rtrim(rtrim($product->minimum_quantity, '0'), '.') }} {{ $product->unit }}</span><span>Stok {{ rtrim(rtrim($product->available_quantity, '0'), '.') }} {{ $product->unit }}</span></div>
                        <a class="button button-outline button-full" href="{{ route('products.show', $product) }}">Lihat &amp; ajukan harga</a>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="pagination-wrap">{{ $products->links() }}</div>
    @else
        <div class="empty-state"><span aria-hidden="true">🧺</span><h3>Belum ada hasil yang cocok</h3><p>Coba ubah kata pencarian, atau kembali lagi saat petani memasang produk baru.</p>@if(request()->query())<a href="{{ route('home') }}">Tampilkan semua produk</a>@endif</div>
    @endif
</section>
@endsection

@push('scripts')
    <script src="{{ asset('js/featured-carousel.js') }}" defer></script>
@endpush
