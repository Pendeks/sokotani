@extends('layouts.app')

@section('title', 'Masuk — SokoTani')

@section('content')
<section class="page-section auth-section">
    <div class="form-card auth-card">
        <p class="eyebrow">SELAMAT DATANG KEMBALI</p>
        <h1>Masuk ke SokoTani</h1>
        <p class="muted">Lanjutkan mengelola penawaran dan pesanan hasil tani.</p>

        @if ($errors->any())
            <div class="alert alert-error" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="stack-form">
            @csrf
            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            </label>
            <label>Kata sandi
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            <button class="button button-primary" type="submit">Masuk</button>
        </form>
        <p class="form-footnote">Belum memiliki akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
    </div>
</section>
@endsection
