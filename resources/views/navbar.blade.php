<nav class="main-header navbar navbar-expand navbar-dark navbar-light fixed-top">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link py-2" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
  </ul>

  <a href="#" class="navbar-brand">
    <span class="brand-text font-weight-light">
      Crane and Trucking Management System
    </span>
  </a>

  <div class="flex items-center ms-auto">
      <!-- Philippine Time Display - Now beside notification -->
      <div class="hidden md:flex items-center text-white mr-4">
          <svg class="w-5 h-5 mr-2 text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
          </svg>
          <span id="philippineTime" class="font-bold text-white"></span>
      </div>
  
      <!-- Notification Bell -->
      @php
          $unreadCount = Auth::user()?->notifications()->unread()->count() ?? 0;
      @endphp
      <button type="button" class="relative mr-4 p-1 text-gray-900 rounded-full hover:bg-gray-800 focus:ring-2 focus:ring-blue-300" data-modal-target="notificationModal" data-modal-toggle="notificationModal">
          <span class="sr-only">View notifications</span>
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
          </svg>
          <div id="notification-badge-pill" class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full -top-1 -right-1" style="{{ $unreadCount > 0 ? '' : 'display:none;' }}">
              {{ $unreadCount }}
          </div>
      </button>

      <div class="flex items-center ms-3">
          <div>
              <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                  <span class="sr-only">Open user menu</span>
                  <img class="w-8 h-8 rounded-full object-cover"
                      src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : asset('images/uploadprof.png') }}"
                      alt="Profile Photo">
              </button>
          </div>

          <div class="z-50 hidden my-4 text-base list-none divide-y divide-white rounded-sm shadow-sm bg-white shadow" id="dropdown-user">
              <!-- Profile Image in dropdown -->
              <div class="flex justify-center items-center p-2">
                  <img class="w-20 h-20 rounded-full shadow-lg object-cover"
                      src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : asset('images/uploadprof.png') }}"
                      alt="Profile Photo">
              </div>

              <!-- User Info -->
              <div class="px-4 py-3 text-center" role="none">
                  <p class="text-sm font-semibold text-gray-900">
                      {{ Auth::user()->name }} {{ Auth::user()->lastname }}
                  </p>
                  <p class="text-sm font-medium text-gray-500 truncate">
                      {{ Auth::user()->email }}
                  </p>
              </div>

              <!-- Dropdown Links -->
              <ul class="py-1" role="none">
                  <li>
                      <a href="{{ route('profile') }}"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                          Profile
                      </a>
                  </li>
                  <li>
                      <a href="{{ route('settings') }}"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                          Settings
                      </a>
                  </li>
                  <li>
                      <form action="{{ route('logout') }}" method="POST">
                          @csrf
                          <button type="submit"
                                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                              Sign out
                          </button>
                      </form>
                  </li>
              </ul>
          </div>
      </div>
  </div>
</nav>
