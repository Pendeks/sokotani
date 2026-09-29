@extends('layouts.app')

@section('title', ($product->exists ? 'Ubah' : 'Pasang').' produk — SokoTani')

@section('content')
<section class="page-section narrow-section">
    <a class="back-link" href="{{ route('farmer.listings.index') }}">← Kembali ke produk saya</a>
    <div class="form-card">
        <p class="eyebrow">TOKO PETANI</p>
        <h1>{{ $product->exists ? 'Perbarui produk' : 'Pasang hasil panen' }}</h1>
        <p class="muted">Cantumkan stok grosir dan harga acuan per unit. Pembeli dapat mengajukan harga berbeda untuk dinegosiasikan.</p>
        @if ($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form method="POST" action="{{ $product->exists ? route('farmer.listings.update', $product) : route('farmer.listings.store') }}" enctype="multipart/form-data" class="stack-form">
            @csrf
            @if ($product->exists) @method('PUT') @endif
            <label>Nama produk
                <input name="name" value="{{ old('name', $product->name) }}" maxlength="160" required placeholder="Contoh: Beras organik panen baru">
            </label>
            <div class="form-row">
                <label>Kategori
                    <input name="category" value="{{ old('category', $product->category) }}" maxlength="80" required placeholder="Sayuran, beras, buah…">
                </label>
                <label>Satuan jual
                    <input name="unit" value="{{ old('unit', $product->unit) }}" maxlength="40" required placeholder="kg, karung 25 kg, peti">
                </label>
            </div>
            <label>Deskripsi
                <textarea name="description" rows="4" maxlength="5000" required placeholder="Jelaskan kualitas, cara budidaya, dan kondisi panen">{{ old('description', $product->description) }}</textarea>
            </label>
            <div class="form-row">
                <label>Stok tersedia ({{ old('unit', $product->unit ?: 'satuan') }})
                    <input type="number" name="available_quantity" value="{{ old('available_quantity', $product->available_quantity) }}" min="0.01" max="9999999999.99" step="0.01" required>
                </label>
                <label>Minimum pembelian
                    <input type="number" name="minimum_quantity" value="{{ old('minimum_quantity', $product->minimum_quantity) }}" min="0.01" max="9999999999.99" step="0.01" required>
                </label>
            </div>
            <div class="form-row">
                <label>Harga acuan per unit (Rp)
                    <input type="number" name="asking_price_per_unit" value="{{ old('asking_price_per_unit', $product->asking_price_per_unit) }}" min="1" max="1000000000000" step="1" required>
                    <small>Harga ini dapat ditawar pembeli.</small>
                </label>
                <label>Perkiraan tanggal panen (opsional)
                    <input type="date" name="harvest_date" value="{{ old('harvest_date', $product->harvest_date?->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}">
                </label>
            </div>
            <label>Lokasi produk
                <input name="location" value="{{ old('location', $product->location) }}" maxlength="160" required placeholder="Desa, kecamatan, kabupaten">
            </label>
            <label>Foto produk (opsional, JPG/PNG/WebP maks. 5 MB)
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            </label>
            @if ($product->exists)
                <label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Tampilkan produk di katalog</label>
            @endif
            <button class="button button-primary" type="submit">{{ $product->exists ? 'Simpan perubahan' : 'Terbitkan produk' }}</button>
        </form>
    </div>
</section>
@endsection
