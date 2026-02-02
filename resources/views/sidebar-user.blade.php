<aside class="main-sidebar sidebar-dark-primary bg-black elevation-4">
    <a href="{{ route('employee.dashboard') }}" class="brand-link logo-switch" style="background-color: black;">
        <h4 class="brand-image-xl logo-xs mb-0 text-center pt-2"><b>ES</b>S</h4>
        <h4 class="brand-image-xl logo-xl mb-0 text-center pt-2 font-bold">Employee Self Service</b></h4>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : asset('images/uploadprof.png') }}" class="img-circle elevation-2" alt="User Image" style="width: 2.1rem; height: 2.1rem; min-width: 2.1rem; min-height: 2.1rem;" />
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }} {{ Auth::user()->lastname }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('employee.dashboard') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg viewBox="0 0 20 20" version="1.1" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff">
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Dribbble-Light-Preview" transform="translate(-340.000000, -5199.000000)" fill="#ffffff">
                                        <g id="icons" transform="translate(56.000000, 160.000000)">
                                            <path d="M294,5041 C298.411,5041 302,5044.589 302,5049 L295.406,5049 L299.197,5045.378 L297.815,5043.932 L292.511,5049 L286,5049 C286,5044.589 289.589,5041 294,5041 M294,5039 C288.477,5039 284,5043.477 284,5049 C284,5054.523 288.477,5059 294,5059 C299.523,5059 304,5054.523 304,5049 C304,5043.477 299.523,5039 294,5039" id="dashboard-[#671]"></path>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Dashboard</span>
                    </a>
                </li>

                <!-- My Attendance -->
                <li class="nav-item">
                    <a href="{{ route('employee.attendance') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('employee.attendance') ? 'active' : '' }}">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#ffffff" viewBox="0 0 32 32" class="w-6 h-6" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM4 16q0 3.264 1.6 6.048t4.384 4.352 6.016 1.6 6.016-1.6 4.384-4.352 1.6-6.048-1.6-6.016-4.384-4.352-6.016-1.632-6.016 1.632-4.384 4.352-1.6 6.016zM14.016 16v-5.984q0-0.832 0.576-1.408t1.408-0.608 1.408 0.608 0.608 1.408v4h4q0.8 0 1.408 0.576t0.576 1.408-0.576 1.44-1.408 0.576h-6.016q-0.832 0-1.408-0.576t-0.576-1.44z"></path>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">My Attendance</span>
                    </a>
                </li>

                <!-- QR code -->
                <li class="nav-item">
                    <a href="{{ route('employee.qr-today') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('employee.qr-today') ? 'active' : '' }}">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#ffffff" viewBox="0 0 32 32" class="w-6 h-6" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM4 16q0 3.264 1.6 6.048t4.384 4.352 6.016 1.6 6.016-1.6 4.384-4.352 1.6-6.048-1.6-6.016-4.384-4.352-6.016-1.632-6.016 1.632-4.384 4.352-1.6 6.016zM14.016 16v-5.984q0-0.832 0.576-1.408t1.408-0.608 1.408 0.608 0.608 1.408v4h4q0.8 0 1.408 0.576t0.576 1.408-0.576 1.44-1.408 0.576h-6.016q-0.832 0-1.408-0.576t-0.576-1.44z"></path>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">My QR Code</span>
                    </a>
                </li>

                <!-- Work Schedule -->
                <li class="nav-item">
                    <a href="{{ route('employee.work-schedule') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('employee.work-schedule') ? 'active' : '' }}">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#fdfcfc" version="1.1" id="Layer_1" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <g>
                                    <g>
                                        <g>
                                            <rect x="234.667" y="373.333" width="42.667" height="42.667"></rect>
                                            <path d="M490.667,53.333H448V32c0-11.797-9.536-21.333-21.333-21.333h-85.333C329.536,10.667,320,20.203,320,32v21.333H192V32 c0-11.797-9.536-21.333-21.333-21.333H85.333C73.536,10.667,64,20.203,64,32v21.333H21.333C9.536,53.333,0,62.869,0,74.667V160 h512V74.667C512,62.869,502.464,53.333,490.667,53.333z M149.333,74.667V96h-42.667V74.667V53.333h42.667V74.667z M405.333,74.667V96h-42.667V74.667V53.333h42.667V74.667z"></path>
                                            <rect x="149.333" y="373.333" width="42.667" height="42.667"></rect>
                                            <rect x="320" y="373.333" width="42.667" height="42.667"></rect>
                                            <rect x="149.333" y="288" width="42.667" height="42.667"></rect>
                                            <path d="M0,480c0,11.797,9.536,21.333,21.333,21.333h469.333c11.797,0,21.333-9.536,21.333-21.333V202.667H0V480z M106.667,352 v-85.333c0-11.797,9.536-21.333,21.333-21.333h85.333h85.333H384c11.797,0,21.333,9.536,21.333,21.333V352v85.333 c0,11.797-9.536,21.333-21.333,21.333h-85.333h-85.333H128c-11.797,0-21.333-9.536-21.333-21.333V352z"></path>
                                            <rect x="320" y="288" width="42.667" height="42.667"></rect>
                                            <rect x="234.667" y="288" width="42.667" height="42.667"></rect>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Work Schedule</span>
                    </a>
                </li>

                <!-- Leave Requests -->
                <li class="nav-item">
                    <a href="{{ route('employee.leave-requests') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('employee.leave-requests') ? 'active' : '' }}">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#ffffff" class="w-6 h-6" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <g>
                                    <g>
                                        <path d="M431.342,25.962h-95.078v-9.266c0-9.22-7.475-16.696-16.696-16.696H192.434c-9.22,0-16.696,7.475-16.696,16.696v9.266 H80.659c-9.22,0-16.696,7.475-16.696,16.696v452.647c0,9.22,7.475,16.696,16.696,16.696H431.34c9.22,0,16.696-7.475,16.696-16.696 V42.657C448.037,33.437,440.562,25.962,431.342,25.962z M209.129,33.391h93.743c0,6.696,0,11.813,0,18.533h-93.743 C209.129,45.22,209.129,40.112,209.129,33.391z M414.646,478.609L414.646,478.609H97.355V59.353h78.383v9.267 c0,9.22,7.475,16.696,16.696,16.696h127.134c9.22,0,16.696-7.475,16.696-16.696v-9.267h78.383V478.609z"></path>
                                    </g>
                                </g>
                                <g>
                                    <g>
                                        <path d="M374.46,98.208H137.54c-9.22,0-16.696,7.475-16.696,16.696v323.519c0,9.22,7.475,16.696,16.696,16.696H374.46 c9.22,0,16.696-7.475,16.696-16.696V114.904C391.155,105.683,383.68,98.208,374.46,98.208z M357.764,421.728H154.236V131.6 h203.528V421.728z"></path>
                                    </g>
                                </g>
                                <g>
                                    <g>
                                        <path d="M309.639,234.808c-6.52-6.52-17.092-6.52-23.611,0l-48.296,48.295l-11.758-11.758c-6.519-6.52-17.091-6.52-23.611,0 c-6.52,6.52-6.52,17.091,0,23.611l23.564,23.564c6.519,6.52,17.091,6.521,23.611,0l60.101-60.101 C316.159,251.898,316.159,241.328,309.639,234.808z"></path>
                                    </g>
                                </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Leave Requests</span>
                    </a>
                </li>

                <!-- Shift Requests -->
                <li class="nav-item">
                    <a href="{{ route('employee.shift-requests') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('employee.shift-requests') ? 'active' : '' }}">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#ffffff" viewBox="0 0 24 24" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Shift Requests</span>
                    </a>
                </li>

                <!-- My Profile -->
                <li class="nav-item">
                    <a href="{{ route('employee.profile') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('employee.profile') ? 'active' : '' }}">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#ffffff" viewBox="0 0 24 24" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" fill="#ffffff"/>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">My Profile</span>
                    </a>
                </li>

                <!-- Logout -->
                <li class="nav-item">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); confirmLogout();" class="nav-link d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#fcfcfc" class="size-6" width="20" height="20">
                                <path d="M15 16.5V19C15 20.1046 14.1046 21 13 21H6C4.89543 21 4 20.1046 4 19V5C4 3.89543 4.89543 3 6 3H13C14.1046 3 15 3.89543 15 5V8.0625M11 12H21M21 12L18.5 9.5M21 12L18.5 14.5" stroke="#ffffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<script>
function confirmLogout() {
    if (confirm('Are you sure you want to logout?')) {
        document.getElementById('logout-form').submit();
    }
}
</script>
