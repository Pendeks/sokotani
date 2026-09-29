@extends('layouts.app')

@section('title', 'Produk saya — SokoTani')

@section('content')
<section class="page-section">
    <div class="section-heading">
        <div><p class="eyebrow">DASHBOARD PETANI</p><h1>Produk saya</h1><p class="muted">Kelola stok dan pantau permintaan harga dari pembeli grosir.</p></div>
        <a class="button button-primary" href="{{ route('farmer.listings.create') }}">+ Pasang produk</a>
    </div>
    <div class="dashboard-links"><a class="button button-outline" href="{{ route('offers.index') }}">Lihat penawaran &amp; pesanan</a></div>

    @if ($products->count())
        <div class="listing-grid">
            @foreach ($products as $product)
                <article class="listing-card">
                    <div class="listing-body">
                        <span class="tag {{ $product->is_active ? '' : 'tag-muted' }}">{{ $product->is_active ? 'Tayang' : 'Disembunyikan' }}</span>
                        <h3>{{ $product->name }}</h3>
                        <p class="listing-location">{{ $product->category }} · {{ $product->location }}</p>
                        <p class="listing-price">Rp{{ number_format($product->asking_price_per_unit, 0, ',', '.') }} <span>/ {{ $product->unit }} · harga dapat ditawar</span></p>
                        <div class="listing-meta"><span>Stok {{ rtrim(rtrim($product->available_quantity, '0'), '.') }} {{ $product->unit }}</span><span>{{ $product->offers_count }} penawaran</span></div>
                        <div class="card-actions">
                            <a class="button button-outline" href="{{ route('farmer.listings.edit', $product) }}">Ubah</a>
                            <a class="button button-outline" href="{{ route('products.show', $product) }}">Lihat</a>
                            @if ($product->is_active)
                                <form method="POST" action="{{ route('farmer.listings.deactivate', $product) }}" onsubmit="return confirm('Sembunyikan produk ini dari katalog?')">@csrf @method('PATCH')<button class="button button-danger-outline" type="submit">Sembunyikan</button></form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="pagination-wrap">{{ $products->links() }}</div>
    @else
        <div class="empty-state"><span aria-hidden="true">🌱</span><h2>Mulai pasang hasil panen Anda</h2><p>Tambahkan produk, stok grosir, dan harga acuan. Pembeli dapat mengajukan tawaran untuk Anda tinjau.</p><a class="button button-primary" href="{{ route('farmer.listings.create') }}">Pasang produk pertama</a></div>
    @endif
</section>
@endsection
