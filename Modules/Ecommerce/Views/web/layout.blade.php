<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f7fb; }
        .shop-shell { min-height: 100vh; }
        .product-card img { height: 220px; object-fit: cover; }
    </style>
</head>
<body>
<div class="shop-shell">
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('ecommerce.shop.index') }}">CMS Shop</a>
            <div class="ms-auto d-flex gap-3">
                <a class="nav-link" href="{{ route('ecommerce.shop.index') }}">Catalog</a>
                <a class="nav-link" href="{{ route('ecommerce.cart.index') }}">Cart</a>
                @auth
                    <a class="nav-link" href="{{ route('ecommerce.orders.index') }}">My Orders</a>
                @endauth
            </div>
        </div>
    </nav>
    <main class="py-5">
        @if(session('success'))
            <div class="container mb-3"><div class="alert alert-success">{{ session('success') }}</div></div>
        @endif
        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
