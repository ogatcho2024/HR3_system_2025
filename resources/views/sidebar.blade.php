<aside class="main-sidebar sidebar-dark-primary bg-black elevation-4">
    <a href="{{ route('dashb') }}" class="brand-link logo-switch" style="background-color: black;">
        <h4 class="brand-image-xl logo-xs mb-0 text-center pt-2"><b>HR</b>3</h4>
        <h4 class="brand-image-xl logo-xl mb-0 text-center pt-2">Human<b>Resources</b></h4>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : asset('images/uploadprof.png') }}" class="img-circle elevation-2" alt="User Image" style="width: 2.1rem; height: 2.1rem; min-width: 2.1rem; min-height: 2.1rem;" />
            </div>
            <div class="info">
                <a href="#" class="d-block text-white font-bold">{{ Auth::user()->name }} {{ Auth::user()->lastname }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Audit Tracking -->
                @if(Auth::user()->isAdmin())
                <li class="nav-item has-treeview {{ request()->routeIs('audit-logs.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }} d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#ffffffff" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" class="icon"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M296 250c-4.4 0-8 3.6-8 8v48c0 4.4 3.6 8 8 8h384c4.4 0 8-3.6 8-8v-48c0-4.4-3.6-8-8-8H296zm184 144H296c-4.4 0-8 3.6-8 8v48c0 4.4 3.6 8 8 8h184c4.4 0 8-3.6 8-8v-48c0-4.4-3.6-8-8-8zm-48 458H208V148h560v320c0 4.4 3.6 8 8 8h56c4.4 0 8-3.6 8-8V108c0-17.7-14.3-32-32-32H168c-17.7 0-32 14.3-32 32v784c0 17.7 14.3 32 32 32h264c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zm440-88H728v-36.6c46.3-13.8 80-56.6 80-107.4 0-61.9-50.1-112-112-112s-112 50.1-112 112c0 50.7 33.7 93.6 80 107.4V764H520c-8.8 0-16 7.2-16 16v152c0 8.8 7.2 16 16 16h352c8.8 0 16-7.2 16-16V780c0-8.8-7.2-16-16-16zM646 620c0-27.6 22.4-50 50-50s50 22.4 50 50-22.4 50-50 50-50-22.4-50-50zm180 266H566v-60h260v60z"></path> </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Audit <br> Tracking</span>
                        <i class="right fas fa-angle-down"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->routeIs('audit-logs.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Logs</p>
                            </a>
                        </li>
                        @if(Auth::user()->isSuperAdmin())
                        <li class="nav-item">
                            <a href="{{ route('audit-logs.security-report') }}" class="nav-link {{ request()->routeIs('audit-logs.security-report') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Security Report</p>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                <div class="w-full h-[1px] bg-gray-500 my-2"></div>

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashb') }}" class="nav-link d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg viewBox="0 0 20 20" version="1.1" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff"><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Dribbble-Light-Preview" transform="translate(-340.000000, -5199.000000)" fill="#ffffff"><g id="icons" transform="translate(56.000000, 160.000000)">
                                <path d="M294,5041 C298.411,5041 302,5044.589 302,5049 L295.406,5049 L299.197,5045.378 L297.815,5043.932 L292.511,5049 L286,5049 C286,5044.589 289.589,5041 294,5041 M294,5039 C288.477,5039 284,5043.477 284,5049 C284,5054.523 288.477,5059 294,5059 C299.523,5059 304,5054.523 304,5049 C304,5043.477 299.523,5039 294,5039" id="dashboard-[#671]"></path></g></g></g></svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Dashboard</span>
                    </a>
                </li>

                <!-- Attendance & Time Tracking -->
                <li class="nav-item has-treeview {{ request()->routeIs('attendanceTimeTracking*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('attendanceTimeTracking*') ? 'active' : '' }} d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#ffffff" viewBox="0 0 32 32" class="w-6 h-6" version="1.1" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>time</title> 
                                <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM4 16q0 3.264 1.6 6.048t4.384 4.352 6.016 1.6 6.016-1.6 4.384-4.352 1.6-6.048-1.6-6.016-4.384-4.352-6.016-1.632-6.016 1.632-4.384 4.352-1.6 6.016zM14.016 16v-5.984q0-0.832 0.576-1.408t1.408-0.608 1.408 0.608 0.608 1.408v4h4q0.8 0 1.408 0.576t0.576 1.408-0.576 1.44-1.408 0.576h-6.016q-0.832 0-1.408-0.576t-0.576-1.44z"></path> </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Attendance &amp;<br> Time Tracking</span>
                        <i class="right fas fa-angle-down"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('attendanceTimeTracking', ['tab' => 'overview']) }}" class="nav-link {{ request()->get('tab') == 'overview' || (!request()->has('tab') && request()->routeIs('attendanceTimeTracking')) ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Overview</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('attendanceTimeTracking', ['tab' => 'realtime']) }}" class="nav-link {{ request()->get('tab') == 'realtime' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Real-time Tracking</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('attendanceTimeTracking', ['tab' => 'clockinout']) }}" class="nav-link {{ request()->get('tab') == 'clockinout' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Clock In/Out</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('attendanceTimeTracking', ['tab' => 'reports']) }}" class="nav-link {{ request()->get('tab') == 'reports' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reports & Analytics</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Work Schedule & Shift Management -->
                <li class="nav-item has-treeview {{ request()->routeIs('workScheduleShiftManagement*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('workScheduleShiftManagement*') ? 'active' : '' }} d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#fdfcfc" version="1.1" id="Layer_1" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" stroke="#fdfcfc"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <rect x="234.667" y="373.333" width="42.667" height="42.667"></rect> <path d="M490.667,53.333H448V32c0-11.797-9.536-21.333-21.333-21.333h-85.333C329.536,10.667,320,20.203,320,32v21.333H192V32 c0-11.797-9.536-21.333-21.333-21.333H85.333C73.536,10.667,64,20.203,64,32v21.333H21.333C9.536,53.333,0,62.869,0,74.667V160 h512V74.667C512,62.869,502.464,53.333,490.667,53.333z M149.333,74.667V96h-42.667V74.667V53.333h42.667V74.667z M405.333,74.667V96h-42.667V74.667V53.333h42.667V74.667z"></path> <rect x="149.333" y="373.333" width="42.667" height="42.667"></rect> <rect x="320" y="373.333" width="42.667" height="42.667"></rect> <rect x="149.333" y="288" width="42.667" height="42.667"></rect> 
                                <path d="M0,480c0,11.797,9.536,21.333,21.333,21.333h469.333c11.797,0,21.333-9.536,21.333-21.333V202.667H0V480z M106.667,352 v-85.333c0-11.797,9.536-21.333,21.333-21.333h85.333h85.333H384c11.797,0,21.333,9.536,21.333,21.333V352v85.333 c0,11.797-9.536,21.333-21.333,21.333h-85.333h-85.333H128c-11.797,0-21.333-9.536-21.333-21.333V352z"></path> <rect x="320" y="288" width="42.667" height="42.667"></rect> <rect x="234.667" y="288" width="42.667" height="42.667"></rect> </g> </g> </g> </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Work Schedule &amp;<br> Shift Management</span>
                        <i class="right fas fa-angle-down"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('workScheduleShiftManagement', ['tab' => 'overview']) }}" class="nav-link {{ request()->get('tab') == 'overview' || (!request()->has('tab') && request()->routeIs('workScheduleShiftManagement')) ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Overview</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('workScheduleShiftManagement', ['tab' => 'shifts']) }}" class="nav-link {{ request()->get('tab') == 'shifts' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Shift Template</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('workScheduleShiftManagement', ['tab' => 'assignments']) }}" class="nav-link {{ request()->get('tab') == 'assignments' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Employee Assignments</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('workScheduleShiftManagement', ['tab' => 'requests']) }}" class="nav-link {{ request()->get('tab') == 'requests' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Shift Requests</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Employee Management System (Admin) -->
                <li class="nav-item has-treeview {{ request()->routeIs('employee-management.dashboard*') ? 'menu-open' : '' }}">
                    <a href="{{ route('employee-management.dashboard') }}" class="nav-link d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#fafafa" class="w-6 h-6" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512.001 512.001" xml:space="preserve" stroke="#fafafa"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> 
                                <path d="M375.071,86.028c-11.366,0-22.143,2.561-31.796,7.122c3.686,4.748,6.998,9.802,9.882,15.121 c2.828,5.216,5.244,10.688,7.214,16.364c3.928,11.321,6.069,23.469,6.069,36.109c0,12.639-2.141,24.788-6.069,36.108 c-1.969,5.678-4.386,11.147-7.214,16.364c-2.884,5.319-6.195,10.372-9.882,15.121c9.653,4.56,20.43,7.123,31.796,7.123 c41.199,0.002,74.716-33.516,74.716-74.714C449.787,119.545,416.27,86.028,375.071,86.028z"></path> </g> </g> <g> <g> <path d="M375.071,271.182c-4.42,0-8.827,0.218-13.206,0.641c6.82,5.311,13.237,11.115,19.187,17.369 c6.005,6.311,11.53,13.079,16.534,20.237c16.349,23.386,27.066,50.987,30.146,80.823c0.607,5.873,0.92,11.83,0.92,17.86 c0,6.261-1.09,12.27-3.072,17.86h68.56c9.864,0,17.86-7.998,17.86-17.86C512.001,332.608,450.574,271.182,375.071,271.182z"></path> </g> </g> <g> <g> <path d="M151.632,196.855c-3.928-11.32-6.069-23.469-6.069-36.108c0-12.64,2.141-24.788,6.069-36.109 c1.971-5.68,4.386-11.15,7.214-16.366c2.884-5.319,6.195-10.372,9.882-15.121c-9.653-4.56-20.43-7.122-31.796-7.122 c-41.199,0-74.716,33.517-74.716,74.716c0,41.198,33.517,74.716,74.716,74.716c11.366,0,22.143-2.562,31.796-7.123 c-3.686-4.749-6.998-9.802-9.882-15.121C156.018,208.002,153.602,202.532,151.632,196.855z"></path> </g> </g> <g> <g> <path d="M136.93,271.182C61.427,271.182,0,332.608,0,408.112c0,9.863,7.997,17.86,17.86,17.86h68.56 c-1.981-5.59-3.071-11.6-3.071-17.86c0-6.031,0.313-11.988,0.919-17.86c3.08-29.836,13.797-57.437,30.146-80.823 c5.005-7.158,10.529-13.926,16.534-20.237c5.95-6.254,12.367-12.058,19.187-17.369C145.757,271.4,141.35,271.182,136.93,271.182z"></path> </g> </g> <g> <g> <path d="M325.393,133.094c-2.509-6.271-5.831-12.13-9.857-17.433c-13.657-17.988-35.257-29.633-59.535-29.633 s-45.878,11.645-59.535,29.635c-4.026,5.303-7.348,11.162-9.857,17.433c-3.421,8.559-5.325,17.883-5.325,27.649 c0,9.765,1.904,19.089,5.325,27.648c2.509,6.271,5.831,12.13,9.857,17.433c13.657,17.988,35.257,29.634,59.535,29.634 s45.878-11.646,59.535-29.636c4.026-5.303,7.348-11.162,9.857-17.433c3.421-8.559,5.325-17.882,5.325-27.648 S328.814,141.653,325.393,133.094z"></path> </g> </g> <g> <g> <path d="M391.768,390.252c-4.11-31.402-18.901-59.488-40.594-80.489c-5.137-4.971-10.656-9.547-16.515-13.672 c-6.044-4.256-12.444-8.04-19.149-11.288c-12.892-6.246-26.905-10.528-41.647-12.457v111.953c0,9.863-7.997,17.86-17.86,17.86 c-9.864,0-17.86-7.998-17.86-17.86V272.346c-14.743,1.929-28.755,6.211-41.648,12.457c-6.705,3.249-13.105,7.032-19.149,11.288 c-5.859,4.126-11.38,8.702-16.515,13.672c-21.695,21-36.485,49.087-40.594,80.489c-0.764,5.846-1.163,11.807-1.163,17.86 c0,9.863,7.997,17.86,17.86,17.86h238.14c9.864,0,17.86-7.998,17.86-17.86C392.933,402.059,392.534,396.098,391.768,390.252z"></path> </g> </g> </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Employee Self <br> Service Management</span>
                        <i class="right fas fa-angle-down"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('employee-management.dashboard', ['tab' => 'overview']) }}" class="nav-link {{ request()->get('tab') == 'overview' || (!request()->has('tab') && request()->routeIs('workScheduleShiftManagement')) ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Overview</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('employee-management.requests') }}" class="nav-link {{ request()->routeIs('employee-management.requests') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('employee-management.employees') }}" class="nav-link {{ request()->routeIs('employee-management.employees') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>User Profiles Management</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('employee-management.employee-portal') }}" class="nav-link {{ request()->routeIs('employee-management.employee-portal') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Employee Portal</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Time Sheet Management -->
                <li class="nav-item has-treeview {{ request()->routeIs('timeSheetManagement*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('timeSheetManagement*') ? 'active' : '' }} d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" stroke="#f5f5f5"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM11 12C10.4477 12 10 12.4477 10 13V17V21C10 21.5523 10.4477 22 11 22H15H21C21.5523 22 22 21.5523 22 21V17V13C22 12.4477 21.5523 12 21 12H15H11ZM12 16V14H14V16H12ZM16 16V14H20V16H16ZM16 20V18H20V20H16ZM14 18V20H12V18H14Z" fill="#ffffffff"></path> </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Timesheet<br> Management</span>
                        <i class="right fas fa-angle-down"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('timeSheetManagement', ['tab' => 'overview']) }}" class="nav-link {{ request()->get('tab') == 'overview' || (!request()->has('tab') && request()->routeIs('timeSheetManagement')) ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Overview</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('timeSheetManagement', ['tab' => 'employees']) }}" class="nav-link {{ request()->get('tab') == 'employees' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Employees Timesheet</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('timeSheetManagement', ['tab' => 'approvals']) }}" class="nav-link {{ request()->get('tab') == 'approvals' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Timesheet Approval</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('timeSheetManagement', ['tab' => 'summary&analytics']) }}" class="nav-link {{ request()->get('tab') == 'summary&analytics' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Summary & Analytics</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Leave Management -->
                <li class="nav-item has-treeview {{ request()->routeIs('leave-management.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('leave-management.*') ? 'active' : '' }} d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg fill="#ffffff" class="w-6 h-6" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> 
                                <path d="M431.342,25.962h-95.078v-9.266c0-9.22-7.475-16.696-16.696-16.696H192.434c-9.22,0-16.696,7.475-16.696,16.696v9.266 H80.659c-9.22,0-16.696,7.475-16.696,16.696v452.647c0,9.22,7.475,16.696,16.696,16.696H431.34c9.22,0,16.696-7.475,16.696-16.696 V42.657C448.037,33.437,440.562,25.962,431.342,25.962z M209.129,33.391h93.743c0,6.696,0,11.813,0,18.533h-93.743 C209.129,45.22,209.129,40.112,209.129,33.391z M414.646,478.609L414.646,478.609H97.355V59.353h78.383v9.267 c0,9.22,7.475,16.696,16.696,16.696h127.134c9.22,0,16.696-7.475,16.696-16.696v-9.267h78.383V478.609z"></path> </g> </g> <g> <g> <path d="M374.46,98.208H137.54c-9.22,0-16.696,7.475-16.696,16.696v323.519c0,9.22,7.475,16.696,16.696,16.696H374.46 c9.22,0,16.696-7.475,16.696-16.696V114.904C391.155,105.683,383.68,98.208,374.46,98.208z M357.764,421.728H154.236V131.6 h203.528V421.728z"></path> </g> </g> <g> <g> <path d="M309.639,234.808c-6.52-6.52-17.092-6.52-23.611,0l-48.296,48.295l-11.758-11.758c-6.519-6.52-17.091-6.52-23.611,0 c-6.52,6.52-6.52,17.091,0,23.611l23.564,23.564c6.519,6.52,17.091,6.521,23.611,0l60.101-60.101 C316.159,251.898,316.159,241.328,309.639,234.808z"></path> </g> </g> </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Leave <br> Management</span>
                        <i class="right fas fa-angle-down"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('leave-management.admin-dashboard') }}" class="nav-link {{ request()->routeIs('leave-management.admin-dashboard') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Overview</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('leave-management.pending-requests') }}" class="nav-link {{ request()->routeIs('leave-management.pending-requests') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Leave Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('leave-management.leave-balances') }}" class="nav-link {{ request()->routeIs('leave-management.leave-balances') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Leave Balances</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('leave-management.calendar') }}" class="nav-link {{ request()->routeIs('leave-management.calendar') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Leave Calendar</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('leave-management.reports-analytics') }}" class="nav-link {{ request()->routeIs('leave-management.reports-analytics') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reports & Analytics</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Logout -->
                <li class="nav-item">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); confirmLogout();" class="nav-link d-flex align-items-center">
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center mr-2">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#fcfcfc" class="size-6" width="20" height="20"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                <path d="M15 16.5V19C15 20.1046 14.1046 21 13 21H6C4.89543 21 4 20.1046 4 19V5C4 3.89543 4.89543 3 6 3H13C14.1046 3 15 3.89543 15 5V8.0625M11 12H21M21 12L18.5 9.5M21 12L18.5 14.5" stroke="#ffffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g>
                            </svg>
                        </span>
                        <span class="nav-text flex-fill text-truncate text-white font-bold">Logout</span>
                    </a>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
