<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: true }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <title>@yield('title', 'Employee Portal')</title>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed flex flex-col overflow-hidden">

<!-- nav bar -->
@include('navbar')
<!-- nav bar -->

<!-- Notification Modal -->
@include('notificationModal')

<div class="flex flex-1">
    <!-- side bar -->
    @include('sidebar-user')
    <!-- sidebar -->

    <!-- content area -->
    <div id="app-content" class="flex-1 flex flex-col transition-all duration-300 mt-16 lg:ml-64">
        <!-- main content -->
        <main class="flex-1 overflow-y-auto p-0 flex flex-col">
            <div class="flex-1">
                @yield('content')
            </div>
            
            <!-- Footer at bottom of content -->
            <footer class="bg-[#111111] text-white py-2 shadow-lg" id="main-footer">
                <div class="flex items-center justify-center gap-3">
                    <img class="rounded-full w-10 h-10" src="{{ asset('images/logo.png') }}" alt="Logo">
                    <p class="text-sm text-gray-300">© 2025 Rest & Feast — All rights reserved.</p>
                </div>
            </footer>
        </main>
    </div>
</div>

<style>
html, body { 
    height: 100vh; 
    margin: 0; 
    padding: 0; 
    overflow: hidden; /* Prevent body scroll */
}

/* Footer styles - at bottom of content */
footer {
    margin: 0 !important;
    padding: 0.5rem 0 0 0 !important;
    margin-bottom: 0 !important;
}

/* Sidebar positioning - full height to bottom */
.main-sidebar {
    bottom: 0 !important;
    height: 100vh !important;
    z-index: 999 !important;
    position: fixed !important;
    left: 0;
    top: 0;
    width: 250px !important;
    transition: transform 0.3s ease-in-out, width 0.3s ease-in-out;
}

/* Mobile: Hide sidebar by default */
@media (max-width: 1023px) {
    .main-sidebar {
        transform: translateX(-100%);
    }
    
    /* Show sidebar when not collapsed on mobile */
    body:not(.sidebar-collapse) .main-sidebar {
        transform: translateX(0);
    }
    
    /* Mobile overlay when sidebar is open */
    body:not(.sidebar-collapse)::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 998;
    }
}

/* Desktop: Sidebar collapse behavior */
@media (min-width: 1024px) {
    .main-sidebar {
        transform: translateX(0) !important;
    }
    
    /* Desktop: Collapse sidebar to icon-only mode */
    body.sidebar-collapse .main-sidebar {
        width: 4.5rem !important;
    }
    
    /* Desktop: adjust content margin when sidebar is collapsed */
    body.sidebar-collapse #app-content {
        margin-left: 4.5rem !important;
    }
}

/* Content area positioning */
#app-content {
    height: calc(100vh - 4rem); /* Account for navbar (4rem) only */
    max-height: calc(100vh - 4rem);
}

/* Main content with flexbox for footer positioning */
main {
    height: 100%;
    max-height: 100%;
    display: flex;
    flex-direction: column;
    min-height: 0; /* Allow flex items to shrink */
}

/* Collapsed sidebar: Desktop icon-only mode */
@media (min-width: 1024px) {
    .sidebar-collapse .nav-sidebar > .nav-item {
        margin-bottom: 2px !important;
    }

    .sidebar-collapse .nav-sidebar > .nav-item > .nav-link {
        padding: 0.5rem !important;
        min-height: 40px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Adjust icon container in collapsed state */
    .sidebar-collapse .nav-icon {
        width: 24px !important;
        height: 24px !important;
        margin-right: 0 !important;
    }

    /* Hide text when collapsed */
    .sidebar-collapse .nav-text {
        display: none !important;
    }

    /* User panel adjustments when collapsed */
    .sidebar-collapse .user-panel .info {
        display: none !important;
    }
    
    .sidebar-collapse .user-panel .image {
        margin: 0 auto !important;
    }
    
    /* Hide brand text when collapsed */
    .sidebar-collapse .brand-link .logo-xl {
        display: none !important;
    }
    
    .sidebar-collapse .brand-link .logo-xs {
        display: block !important;
    }
}

/* Re-enable click events */
body.sidebar-collapse .main-sidebar .nav-sidebar .nav-item .nav-link {
    pointer-events: auto !important;
}
</style>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App - DISABLED to prevent conflicts -->
<!-- <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
<script>
    function displayPhilippineTime() {
        // Create a date object for Philippine time (UTC+8)
        const options = {
            timeZone: 'Asia/Manila',
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        
        const now = new Date();
        const philippineTime = now.toLocaleDateString('en-US', options);
        
        const timeElement = document.getElementById('philippine-time');
        if (timeElement) {
            timeElement.textContent = philippineTime;
        }
    }

    // Update time every second
    setInterval(displayPhilippineTime, 1000);
    // Display time immediately
    displayPhilippineTime();

    // ===== SIDEBAR TOGGLE SYSTEM =====
    // Custom implementation without AdminLTE conflicts
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initializing Sidebar Toggle System');
        
        const sidebarToggle = document.querySelector('[data-widget="pushmenu"]');
        const sidebar = document.querySelector('.main-sidebar');
        const appContent = document.querySelector('#app-content');
        
        // Debug: Check if elements exist
        console.log('Sidebar Toggle Button:', sidebarToggle);
        console.log('Sidebar Element:', sidebar);
        console.log('App Content:', appContent);
        
        // Initialize: Mobile starts collapsed, Desktop starts open
        function initializeSidebar() {
            if (window.innerWidth < 1024) {
                document.body.classList.add('sidebar-collapse');
                console.log('📱 Mobile mode: Sidebar hidden');
            } else {
                document.body.classList.remove('sidebar-collapse');
                console.log('🖥️ Desktop mode: Sidebar visible');
            }
        }
        
        initializeSidebar();
        
        // Toggle sidebar on button click
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isCollapsed = document.body.classList.contains('sidebar-collapse');
                
                if (isCollapsed) {
                    document.body.classList.remove('sidebar-collapse');
                    console.log('✅ Sidebar OPENED');
                } else {
                    document.body.classList.add('sidebar-collapse');
                    console.log('❌ Sidebar CLOSED');
                }
            });
            console.log('✅ Toggle button listener attached');
        } else {
            console.error('❌ Toggle button not found!');
        }
        
        // Close sidebar when clicking overlay on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 1024 && 
                !document.body.classList.contains('sidebar-collapse') &&
                !e.target.closest('.main-sidebar') && 
                !e.target.closest('[data-widget="pushmenu"]')) {
                document.body.classList.add('sidebar-collapse');
                console.log('📱 Sidebar closed by overlay click');
            }
        });
        
        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                initializeSidebar();
            }, 250);
        });
    });
</script>

</body>
</html>
