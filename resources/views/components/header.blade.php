<header class="navbar-header">
    <nav class="navbar-main" aria-label="Main Navigation">
        <div class="navbar-left">
            <a href="{{ route('home') }}" class="navbar-logo">SokoTani</a>
        </div>
        <div class="navbar-center">
            <div class="navbar-center-top">
                <form class="navbar-search" method="GET" action="{{ route('home') }}" role="search">
                    <button class="navbar-search-btn" type="submit" aria-label="Cari produk">🔍</button>
                    <input type="search" name="q" class="navbar-search-input" value="{{ request('q') }}" placeholder="Cari hasil tani atau lokasi" aria-label="Cari hasil tani atau lokasi">
                </form>
            </div>
            <nav class="navbar-categories" aria-label="Kategori">
                <ul>
                    <li><a href="#">Beras</a></li>
                    <li><a href="#">Padi</a></li>
                    <li><a href="#">Telur</a></li>
                    <li><a href="#">Kedelai</a></li>
                    <li><a href="#">Beras Merah</a></li>
                    <li><a href="#">Kacang Hijau</a></li>
                </ul>
            </nav>
        </div>
        <div class="navbar-right">
            <div class="navbar-auth">
                @auth
                    @if (auth()->user()->role === 'farmer')
                        <a class="account-dashboard-link" href="{{ route('farmer.listings.index') }}"><span aria-hidden="true">▣</span> Toko saya</a>
                        <a class="account-dashboard-link account-profile-link" href="{{ route('farmer.profile.edit') }}"><span aria-hidden="true">♙</span> Profil</a>
                    @elseif (auth()->user()->role === 'admin')
                        <a class="account-dashboard-link" href="{{ route('admin.farmers.index') }}"><span aria-hidden="true">✓</span> Verifikasi</a>
                    @else
                        <a class="account-dashboard-link" href="{{ route('offers.index') }}"><span aria-hidden="true">▤</span> Penawaran saya</a>
                    @endif
                    <a class="notification-nav-link" href="{{ route('notifications.index') }}" aria-label="Notifikasi{{ auth()->user()->unreadNotifications()->count() ? ', '.auth()->user()->unreadNotifications()->count().' belum dibaca' : '' }}" title="Notifikasi">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 22a2.2 2.2 0 0 0 2.18-2h-4.36A2.2 2.2 0 0 0 12 22Zm7-6-1.6-1.8V10a5.4 5.4 0 0 0-4.4-5.3V4a1 1 0 0 0-2 0v.7A5.4 5.4 0 0 0 6.6 10v4.2L5 16v1h14v-1Z"/></svg>
                        @if (($unreadNotificationsCount = auth()->user()->unreadNotifications()->count()) > 0)<span class="notification-count">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>@endif
                    </a>
                    <details class="account-menu">
                        <summary class="account-menu-toggle" aria-label="Menu akun {{ auth()->user()->name }}" title="Menu akun">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 12a4.25 4.25 0 1 0 0-8.5A4.25 4.25 0 0 0 12 12Zm0 2c-4.42 0-8 2.24-8 5v1.5h16V19c0-2.76-3.58-5-8-5Z"/></svg>
                            <svg class="account-chevron" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="m5 7.5 5 5 5-5"/></svg>
                        </summary>
                        <div class="account-menu-panel">
                            <div class="account-identity">
                                <span class="account-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 12a4.25 4.25 0 1 0 0-8.5A4.25 4.25 0 0 0 12 12Zm0 2c-4.42 0-8 2.24-8 5v1.5h16V19c0-2.76-3.58-5-8-5Z"/></svg></span>
                                <span><strong>{{ auth()->user()->name }}</strong><small>{{ ['farmer' => 'Akun petani', 'admin' => 'Admin SokoTani', 'buyer' => 'Akun pembeli'][auth()->user()->role] ?? 'Akun SokoTani' }}</small></span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4H5.5A1.5 1.5 0 0 0 4 5.5v13A1.5 1.5 0 0 0 5.5 20H10v-2H6V6h4V4Zm5.59 3.59L14.17 9 16.17 11H9v2h7.17l-2 2 1.42 1.41L20 12l-4.41-4.41Z"/></svg>Keluar</button></form>
                        </div>
                    </details>
                @else
                    <a class="button button-login" href="{{ route('login') }}">Masuk</a>
                    <a class="button button-signup" href="{{ route('register') }}">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>
</header>
