@extends('layouts.app')

@section('title', 'Profil petani — SokoTani')

@section('content')
<section class="page-section narrow-section">
    <a class="back-link" href="{{ route('farmer.listings.index') }}">← Kembali ke toko saya</a>
    <div class="form-card">
        <p class="eyebrow">IDENTITAS &amp; KEPERCAYAAN</p>
        <h1>Profil petani</h1>
        <p class="muted">Profil yang lengkap membantu pembeli mengenal petani dan hasil panennya. Nomor telepon hanya untuk pengelolaan akun dan tidak ditampilkan di profil publik.</p>
        @if ($farmer->is_verified)
            <div class="verification-notice verification-success"><strong>✓ Petani terverifikasi</strong><span>Profil Anda telah ditinjau oleh admin SokoTani.</span></div>
        @else
            <div class="verification-notice"><strong>Profil belum terverifikasi</strong><span>Lengkapi informasi kebun. Admin akan meninjau profil sebelum memberikan tanda verifikasi.</span></div>
        @endif
        @if ($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('farmer.profile.update') }}" class="stack-form">
            @csrf @method('PUT')
            <label>Nama tampilan
                <input name="name" value="{{ old('name', $farmer->name) }}" maxlength="255" required>
            </label>
            <label>Nomor telepon (pribadi)
                <input name="phone" type="tel" value="{{ old('phone', $farmer->phone) }}" maxlength="30" autocomplete="tel" placeholder="Opsional">
            </label>
            <label>Lokasi kebun / usaha tani
                <input name="farm_location" value="{{ old('farm_location', $farmer->farm_location) }}" maxlength="160" required placeholder="Desa, kecamatan, kabupaten">
            </label>
            <label>Tentang kebun dan cara bertani
                <textarea name="farm_description" rows="5" maxlength="3000" required placeholder="Ceritakan hasil tani, pengalaman, dan praktik pertanian Anda">{{ old('farm_description', $farmer->farm_description) }}</textarea>
            </label>
            <button class="button button-primary" type="submit">Simpan profil</button>
        </form>
        @if ($farmer->farm_location)
            <p class="form-footnote">Profil publik: <a href="{{ route('farmers.show', $farmer) }}" target="_blank" rel="noopener">Lihat profil petani →</a></p>
        @endif
    </div>
</section>
@endsection
