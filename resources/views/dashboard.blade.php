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

    <title>Dashboard</title>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed flex flex-col overflow-hidden">

<!-- nav bar -->
@include('navbar')
<!-- nav bar -->

<!-- Notification Modal -->
@include('notificationModal')

<div class="flex flex-1">
    <!-- side bar -->
    @include('sidebar')
    <!-- sidebar -->

    <!-- content area -->
    <div id="app-content" class="flex-1 flex flex-col transition-all duration-300 mt-16 ml-64 min-w-0">
        <!-- main content -->
        <main class="flex-1 overflow-y-auto p-0">
            <div>
                @yield('content')
            </div>
            
            <!-- Footer at bottom of page content -->
             @if (!View::hasSection('noFooter'))
            <footer class="bg-[#111111] text-white py-2 shadow-lg" id="main-footer">
                <div class="flex items-center justify-center gap-3">
                    <img class="rounded-full w-10 h-10" src="{{ asset('images/logo.png') }}" alt="Logo">
                    <p class="text-sm text-gray-300">© 2025 Rest & Feast — All rights reserved.</p>
                </div>
            </footer>
            @endif
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
    width: calc(100% - 16rem);
    margin-left: 16rem;
    min-width: 0;
}

body.sidebar-collapse #app-content {
    width: calc(100% - 4rem);
    margin-left: 4rem;
}

/* Main content with scrolling */
main {
    height: 100%;
    max-height: 100%;
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

/* Dropdown/Treeview Styling */
.nav-treeview {
    display: none;
    padding-left: 0.5rem;
    overflow: hidden;
}

.nav-item.menu-open > .nav-treeview {
    display: block;
}

/* Arrow rotation animation */
.nav-item .fa-angle-left {
    transition: transform 0.3s ease !important;
}

/* Active dropdown styling */
.nav-item.has-treeview.menu-open > .nav-link {
    background-color: rgba(255, 255, 255, 0.1) !important;
}

/* Sub-menu item styling */
.nav-treeview .nav-item .nav-link {
    padding-left: 3rem !important;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8) !important;
}

.nav-treeview .nav-item .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: #fff !important;
}

.nav-treeview .nav-item .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}

/* Ensure treeview items are visible */
.nav-treeview .nav-item {
    display: block;
}

/* Collapsed sidebar treeview handling */
.sidebar-collapse .nav-item.has-treeview > .nav-treeview {
    display: none !important;
}

.sidebar-collapse .nav-item.has-treeview > .nav-link .fa-angle-left {
    display: none !important;
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

        // Get the formatted date and time string
        const philippineDateTime = new Date().toLocaleString('en-PH', options);

        // Update the element with the current time
        const timeElement = document.getElementById('philippineTime');
        if (timeElement) {
            timeElement.textContent = philippineDateTime;
        }
    }

    // Initial call to display the time
    displayPhilippineTime();

    // Update the time every second
    setInterval(displayPhilippineTime, 1000);

    // Add event listener to ensure the function runs after DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        displayPhilippineTime();
        makeDropdownClosable();
    });

    // Logout confirmation function
    function confirmLogout() {
        if (confirm('Are you sure you want to logout?')) {
            document.getElementById('logout-form').submit();
        }
    }

    // Make dropdown menu closable
    function makeDropdownClosable() {
        const attendanceDropdown = document.querySelector('.nav-item.has-treeview');
        const dropdownLink = attendanceDropdown?.querySelector('a[data-widget="treeview"]');
        
        if (dropdownLink && attendanceDropdown) {
            dropdownLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Toggle the menu-open class
                if (attendanceDropdown.classList.contains('menu-open')) {
                    attendanceDropdown.classList.remove('menu-open');
                    // Rotate arrow icon back
                    const arrow = this.querySelector('.fa-angle-left');
                    if (arrow) {
                        arrow.style.transform = 'rotate(0deg)';
                    }
                } else {
                    attendanceDropdown.classList.add('menu-open');
                    // Rotate arrow icon down
                    const arrow = this.querySelector('.fa-angle-left');
                    if (arrow) {
                        arrow.style.transform = 'rotate(-90deg)';
                    }
                }
            });
        }
    }
</script>
<script>
  // Sync content margin with AdminLTE PushMenu
  $(document).on('collapsed.lte.pushmenu', function() {
    $('#app-content').removeClass('ml-64').addClass('ml-20');
  });
  $(document).on('shown.lte.pushmenu', function() {
    $('#app-content').removeClass('ml-20').addClass('ml-64');
  });
</script>
</body>
</html>
