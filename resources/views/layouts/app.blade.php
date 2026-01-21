<!DOCTYPE html>
<html>
<head>
    <title>Smart Warehouse Monitoring</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    @php
        $colors = json_decode(request()->cookie('theme_colors'), true) ?? [
            'sidebar_bg' => '#2C3E50',
            'sidebar_text' => '#ECF0F1',
            'topbar_bg' => '#E5E5E5',
            'menu_hover' => '#D0D0D0',
            'menu_active' => '#FFFFFF',
        ];
    @endphp

    <style>
        body {
            overflow-x: hidden;
        }

        .sidebar {
            width: 260px;
            background: {{ $colors['sidebar_bg'] }};
            color: {{ $colors['sidebar_text'] }};
            min-height: 100vh;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .sidebar.collapsed {
            width: 0;
            padding: 0;
            overflow: hidden;
        }

        .sidebar h5 {
            font-weight: 700;
            margin-bottom: 30px;
        }

        .menu-item {
            background: transparent;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            color: {{ $colors['sidebar_text'] }};
            transition: background 0.3s ease;
        }

        .menu-item:hover {
            background: {{ $colors['menu_hover'] }};
            color: #333;
        }

        .menu-item.active {
            background: {{ $colors['menu_active'] }};
            color: #333;
        }

        .topbar {
            background: {{ $colors['topbar_bg'] }};
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toggle-sidebar {
            background: none;
            border: 2px solid #333;
            font-size: 28px;
            cursor: pointer;
            padding: 8px 12px;
            margin-right: 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
            color: #333;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-sidebar:hover {
            background: {{ $colors['menu_hover'] }};
            transform: scale(1.1);
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 5px 10px;
            transition: all 0.3s ease;
        }

        .profile-btn:hover {
            opacity: 0.7;
            transform: scale(1.1);
        }

        .profile-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1000;
            margin-top: 5px;
        }

        .profile-menu.show {
            display: block;
        }

        .profile-menu a, .profile-menu button {
            display: block;
            width: 100%;
            padding: 12px 15px;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            color: #333;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .profile-menu a:hover, .profile-menu button:hover {
            background: #f5f5f5;
        }

        .profile-menu button.logout {
            color: #dc3545;
            border-top: 1px solid #eee;
        }

        .profile-menu button.logout:hover {
            background: #ffe0e0;
        }

        /* Tampilkan toggle button di semua ukuran layar */
        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                z-index: 999;
                height: 100vh;
                padding-top: 80px;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
                width: 260px;
            }

            .d-flex {
                position: relative;
            }
        }
    </style>
</head>

<body>

<div class="d-flex">

    {{-- SIDEBAR --}}
    <div class="sidebar" id="sidebar">
        <h5>SMART WAREHOUSE<br>MONITORING</h5>

        <a href="/dashboard"
           class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
            📊 Dashboard Utama
        </a>

        <a href="/monitoring"
           class="menu-item {{ request()->is('monitoring') ? 'active' : '' }}">
            📊 Monitoring System
        </a>

        <a href="/laporan"
           class="menu-item {{ request()->is('laporan') ? 'active' : '' }}">
            🧾 Laporan
        </a>
        
        <a href="/settings"
           class="menu-item {{ request()->is('settings') ? 'active' : '' }}">
            ⚙️ Pengaturan
        </a>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="flex-grow-1">

        {{-- TOPBAR --}}
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="toggle-sidebar" id="toggleSidebar" title="Toggle Sidebar">
                    ☰
                </button>
                <div id="dateTimeDisplay">
                    <strong id="dateDisplay">{{ now()->translatedFormat('l, d F Y') }}</strong><br>
                    <span id="timeDisplay">{{ now()->format('H:i:s') }}</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span>Selamat Datang, {{ Auth::user()->name }}!</span>
                <div class="profile-dropdown">
                    <button class="profile-btn" id="profileBtn" title="Profile">
                        👤
                    </button>
                    <div class="profile-menu" id="profileMenu">
                        <a href="{{ route('profile.index') }}">
                            👤 Profil Saya
                        </a>
                        <form action="{{ route('profile.logout') }}" method="POST" style="margin: 0;" class="logout-form">
                            @csrf
                            <button type="submit" class="logout">
                                🚪 Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- PAGE CONTENT --}}
        <div class="p-4">
            @yield('content')
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    const dateDisplay = document.getElementById('dateDisplay');
    const timeDisplay = document.getElementById('timeDisplay');

    // Real-time Clock Update
    function updateDateTime() {
        const now = new Date();
        
        // Format waktu (HH:i:s)
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        timeDisplay.textContent = `${hours}:${minutes}:${seconds}`;

        // Format tanggal (hari, tanggal bulan tahun)
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        const dayName = days[now.getDay()];
        const date = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        
        dateDisplay.textContent = `${dayName}, ${date} ${monthName} ${year}`;
    }

    // Update waktu setiap 1 detik
    updateDateTime();
    setInterval(updateDateTime, 1000);

    // Toggle Sidebar
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('collapsed');
        });
    }

    // Toggle Profile Menu
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileMenu.contains(e.target) && e.target !== profileBtn) {
                profileMenu.classList.remove('show');
            }
        });

        // Close menu when clicking on a link
        const profileLinks = profileMenu.querySelectorAll('a, button');
        profileLinks.forEach(link => {
            link.addEventListener('click', function() {
                profileMenu.classList.remove('show');
            });
        });
    }

    // Handle Logout Confirmation
    const logoutForms = document.querySelectorAll('.logout-form');
    logoutForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (confirm('Apakah anda ingin logout?')) {
                form.submit();
            }
        });
    });

    // Close sidebar when clicking menu items on mobile/tablet
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                sidebar.classList.add('collapsed');
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('collapsed');
        }
    });
});
</script>

</body>
@stack('scripts')
</html>
