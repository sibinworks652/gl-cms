@extends('admin.layouts.app')

@section('content')

@push('styles')
<style>

/* ===== HEADER ===== */
.header {
    background: #222;
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 1000;
    width: 100%;
    max-width: 80%;
    
    margin: 0 auto;
}

.container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
}

.logo {
    font-size: 20px;
    font-weight: bold;
}

/* ===== MENU ===== */
.menu {
    list-style: none;
    display: flex;
    margin:0;
    padding: 0;
}

.menu li {
    position: relative;
}

.menu li a {
    display: block;
    padding: 10px 15px;
    color: #fff;
    text-decoration: none;
}

.menu li a:hover {
    background: #444;
}

/* ===== SUBMENU ===== */
.submenu {
    position: absolute;
    top: 100%;
    left: 0;
    background: #333;
    list-style: none;
    min-width: 250px;
    display: none;
    margin: 0;
    padding: 0;
}
/* Default (open to right) */
.submenu .submenu {
    top: 0;
    left: 100%;
}

/* Flip to left */
.submenu-left {
    left: auto !important;
    right: 100%;
}
/* Show first level */
.dropdown:hover > .submenu {
    display: block;
}

/* Nested dropdown parent */
.submenu .dropdown {
    position: relative;
}

/* Nested submenu (right side) */
.submenu .submenu {
    top: 0;
    left: 100%;
}

/* Show nested submenu */
.submenu .dropdown:hover > .submenu {
    display: block;
}

/* ===== HAMBURGER ===== */
.menu-icon {
    display: none;
    font-size: 24px;
    cursor: pointer;
}

#menu-toggle {
    display: none;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {

    .menu-icon {
        display: block;
    }

    .nav {
        position: absolute;
        top: 60px;
        left: 0;
        width: 100%;
        background: #222;
        display: none;
    }

    .menu {
        flex-direction: column;
    }

    .menu li {
        border-top: 1px solid #333;
    }

    #menu-toggle:checked + .menu-icon + .nav {
        display: block;
    }

    /* Mobile submenu */
    .submenu {
        position: static;
        display: none;
    }

    .dropdown.active > .submenu {
        display: block;
    }
}
/* Menu link layout */
.menu li a,
.submenu li a {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Arrow base */
.menu-arrow-header {
    margin-top:3px;
    font-size: 11px;
    margin-left: 8px;
    transition: transform 0.2s ease;
}

/* Down arrow (top level) */
.arrow-down {
    transform: rotate(0deg);
}

/* Right arrow (submenu) */
.arrow-right {
    transform: rotate(0deg);
}

/* Hover animations */
.dropdown:hover > a .arrow-down {
    transform: rotate(180deg); /* ▼ → ▲ */
}

.submenu .dropdown:hover > a .arrow-right {
    transform: rotate(90deg); /* ▶ → ▼ */
}
</style>
@endpush
@php($headerMenu = $dynamicMenus->get('header'))
<header class="header">
    <div class="container">

        <div class="logo">MyLogo</div>

        <!-- Hamburger -->
        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-icon">&#9776;</label>

        <nav class="nav">

            {{-- 🔥 Dynamic Menu --}}
            @include('menu::partials.render', [
                'items' => $headerMenu->rootItems,
                'class' => 'menu'
            ])

        </nav>

    </div>
</header>
@push('scripts')
<script>
function fixSubmenuPosition() {
    document.querySelectorAll('.submenu').forEach(menu => {

        const rect = menu.getBoundingClientRect();

        // Reset first
        menu.classList.remove('submenu-left');

        // If overflowing right
        if (rect.right > window.innerWidth) {
            menu.classList.add('submenu-left');
        }
    });
}

// Run on hover
document.querySelectorAll('.dropdown').forEach(item => {
    item.addEventListener('mouseenter', fixSubmenuPosition);
});

// Also on resize
window.addEventListener('resize', fixSubmenuPosition);
</script>
@endpush
@endsection
