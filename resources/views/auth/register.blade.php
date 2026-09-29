@extends('layouts.app')

@section('title', 'Daftar akun — SokoTani')

@section('content')
<section class="page-section auth-section">
    <div class="form-card auth-card">
        <p class="eyebrow">MULAI BERDAGANG</p>
        <h1>Bergabung dengan SokoTani</h1>
        <p class="muted">Daftar sebagai petani untuk memasang hasil panen, atau sebagai pembeli untuk mengajukan penawaran grosir.</p>

        @if ($errors->any())
            <div class="alert alert-error" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="stack-form">
            @csrf
            <label>Nama lengkap
                <input name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name">
            </label>
            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </label>
            <label>Saya ingin bergabung sebagai
                <select name="role" required>
                    <option value="buyer" @selected(old('role', 'buyer') === 'buyer')>Pembeli grosir</option>
                    <option value="farmer" @selected(old('role') === 'farmer')>Petani / penjual</option>
                </select>
            </label>
            <label>Kata sandi
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label>
            <label>Ulangi kata sandi
                <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
            </label>
            <button class="button button-primary" type="submit">Buat akun</button>
        </form>
        <p class="form-footnote">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</section>
@endsection
