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
    <div id="app-content" class="flex-1 flex flex-col transition-all duration-300 mt-16 ml-64">
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

/* Collapsed sidebar: tighter spacing for nav items */
.sidebar-collapse .nav-sidebar > .nav-item {
    margin-bottom: 2px !important;
}

.sidebar-collapse .nav-sidebar > .nav-item > .nav-link {
    padding: 0.25rem 0.75rem !important;
    min-height: 35px !important;
}

/* Adjust icon container in collapsed state */
.sidebar-collapse .nav-icon {
    width: 20px !important;
    height: 20px !important;
    margin-right: 17 !important;
}

/* Hide text and center icons when collapsed */
.sidebar-collapse .nav-text {
    display: none !important;
}

.sidebar-collapse .nav-link {
    justify-content: center !important;
}

/* User panel adjustments when collapsed */
.sidebar-collapse .user-panel {
    text-align: center;
}

.sidebar-collapse .user-panel .info {
    display: none !important;
}

/* Re-enable click events but disable hover styling */
body.sidebar-collapse .main-sidebar .nav-sidebar .nav-item .nav-link {
    pointer-events: auto !important;
}
</style>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
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

    // Toggle sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.querySelector('[data-widget="pushmenu"]');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapse');
            });
        }
    });
</script>

</body>
</html>
