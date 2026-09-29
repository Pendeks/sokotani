<li class="product-card">
    <img src="{{ $image }}" alt="{{ $title }}" class="product-image">
    <div class="product-info">
        <h3>{{ $title }}</h3>
        <div class="product-price">{{ $price }}</div>
        <div class="product-location">
            <div class="location-icon"></div>
            <span>{{ $seller }}</span>
        </div>
    </div>
</li>
