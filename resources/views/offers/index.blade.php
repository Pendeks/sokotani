@extends('layouts.app')

@section('title', 'Penawaran & pesanan — SokoTani')

@section('content')
<section class="page-section">
    @if ($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="section-heading">
        <div><p class="eyebrow">NEGOSIASI LANGSUNG</p><h1>Penawaran &amp; pesanan</h1><p class="muted">Semua harga adalah per unit. Harga yang disepakati disimpan bersama pesanan.</p></div>
        @if (auth()->user()->role === 'buyer')<a class="button button-outline" href="{{ route('home') }}">Jelajahi produk</a>@else<a class="button button-outline" href="{{ route('farmer.listings.index') }}">Produk saya</a>@endif
    </div>

    @if ($offers->count())
        <div class="offers-list">
            @foreach ($offers as $offer)
                @php
                    $statusLabels = ['pending' => 'Menunggu tanggapan', 'countered' => 'Menunggu respons Anda', 'accepted' => 'Disepakati', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan', 'completed' => 'Selesai'];
                    $actionLabels = ['buyer_offer' => 'Pembeli mengajukan', 'farmer_counter' => 'Petani membalas harga', 'buyer_counter' => 'Pembeli mengajukan harga baru', 'accepted' => 'Harga disepakati', 'farmer_rejected' => 'Petani menolak', 'buyer_declined' => 'Pembeli membatalkan', 'completed' => 'Pesanan diselesaikan'];
                @endphp
                <article class="offer-card-item">
                    <div class="offer-card-heading">
                        <div><span class="tag">{{ $offer->product->category }}</span><h2><a href="{{ route('products.show', $offer->product) }}">{{ $offer->product->name }}</a></h2><p class="muted">{{ auth()->user()->role === 'farmer' ? 'Pembeli: '.$offer->buyer->name : 'Petani: '.$offer->product->farmer->name }}</p></div>
                        <span class="status-pill status-{{ $offer->status }}">{{ $statusLabels[$offer->status] ?? ucfirst($offer->status) }}</span>
                    </div>
                    <div class="offer-summary">
                        <div><span>Jumlah</span><strong>{{ rtrim(rtrim($offer->quantity, '0'), '.') }} {{ $offer->product->unit }}</strong></div>
                        <div><span>Harga saat ini / unit</span><strong>Rp{{ number_format($offer->current_unit_price, 0, ',', '.') }}</strong></div>
                        @if ($offer->agreed_unit_price)<div><span>Harga disepakati / unit</span><strong>Rp{{ number_format($offer->agreed_unit_price, 0, ',', '.') }}</strong></div><div><span>Total nilai barang</span><strong>Rp{{ number_format($offer->agreed_unit_price * (float) $offer->quantity, 0, ',', '.') }}</strong></div>@endif
                        <div><span>Pengambilan</span><strong>{{ $offer->fulfillment_method === 'pickup' ? 'Ambil di petani' : 'Pengiriman petani' }}</strong></div>
                    </div>
                    @if ($offer->fulfillment_notes)<p class="offer-note"><strong>Catatan pengiriman/pengambilan:</strong> {{ $offer->fulfillment_notes }}</p>@endif

                    <div class="negotiation-history">
                        <h3>Riwayat negosiasi</h3>
                        <ol>
                            @foreach ($offer->negotiations as $negotiation)
                                <li><div><strong>{{ $actionLabels[$negotiation->action] ?? ucfirst(str_replace('_', ' ', $negotiation->action)) }}</strong><span>{{ $negotiation->user->name }} · {{ $negotiation->created_at->format('d/m/Y H:i') }}</span></div>@if($negotiation->unit_price)<b>Rp{{ number_format($negotiation->unit_price, 0, ',', '.') }} / {{ $offer->product->unit }}</b>@endif @if($negotiation->message)<p>{{ $negotiation->message }}</p>@endif</li>
                            @endforeach
                        </ol>
                    </div>

                    @if (auth()->user()->role === 'farmer' && $offer->status === 'pending')
                        <div class="response-panel"><h3>Tanggapi penawaran</h3><form method="POST" action="{{ route('farmer.offers.respond', $offer) }}" class="response-form">@csrf<label class="sr-only" for="farmer-message-{{ $offer->id }}">Pesan untuk pembeli</label><input id="farmer-message-{{ $offer->id }}" name="message" maxlength="1000" placeholder="Pesan opsional untuk pembeli"><label class="counter-price-field">Harga balasan (Rp / {{ $offer->product->unit }})<input type="number" name="unit_price" min="1" max="1000000000000" placeholder="Untuk opsi tawar"></label><div class="card-actions"><button class="button button-primary" name="action" value="accept" type="submit">Terima harga</button><button class="button button-outline" name="action" value="counter" type="submit" onclick="return this.form.unit_price.value !== '' || (alert('Isi harga balasan terlebih dahulu.'), false)">Ajukan harga balasan</button><button class="button button-danger-outline" name="action" value="reject" type="submit">Tolak</button></div></form></div>
                    @elseif (auth()->user()->role === 'buyer' && in_array($offer->status, ['pending', 'countered'], true))
                        <div class="response-panel"><h3>{{ $offer->status === 'countered' ? 'Tanggapi harga balasan petani' : 'Batalkan penawaran' }}</h3><form method="POST" action="{{ route('offers.buyer.respond', $offer) }}" class="response-form">@csrf @if($offer->status === 'countered')<label class="counter-price-field">Tawaran harga baru (Rp / {{ $offer->product->unit }})<input type="number" name="unit_price" min="1" max="1000000000000" placeholder="Untuk mengajukan tawaran baru"></label>@endif<label class="response-message">Pesan opsional<input name="message" maxlength="1000" placeholder="Catatan untuk petani"></label><div class="card-actions">@if($offer->status === 'countered')<button class="button button-primary" name="action" value="accept" type="submit">Terima harga petani</button><button class="button button-outline" name="action" value="counter" type="submit" onclick="return this.form.unit_price.value !== '' || (alert('Isi tawaran harga baru terlebih dahulu.'), false)">Ajukan harga baru</button>@endif<button class="button button-danger-outline" name="action" value="decline" type="submit">{{ $offer->status === 'countered' ? 'Tolak dan batalkan' : 'Batalkan penawaran' }}</button></div></form></div>
                    @elseif (auth()->user()->role === 'farmer' && $offer->status === 'accepted')
                        <form method="POST" action="{{ route('farmer.offers.complete', $offer) }}" class="complete-form">@csrf<button class="button button-primary" type="submit">Tandai pesanan selesai</button></form>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="pagination-wrap">{{ $offers->links() }}</div>
    @else
        <div class="empty-state"><span aria-hidden="true">🤝</span><h2>Belum ada penawaran</h2><p>{{ auth()->user()->role === 'farmer' ? 'Penawaran dari pembeli akan muncul di sini.' : 'Jelajahi katalog, lalu ajukan jumlah dan harga untuk mulai bernegosiasi.' }}</p>@if(auth()->user()->role === 'buyer')<a class="button button-primary" href="{{ route('home') }}">Cari hasil tani</a>@endif</div>
    @endif
</section>
@endsection
