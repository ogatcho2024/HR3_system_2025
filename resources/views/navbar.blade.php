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

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto d-flex align-items-center">
    <!-- Notifications -->
    <li class="nav-item">
      @include('components.notification-dropdown')
    </li>
    
    <li class="nav-item mr-2">
      <div class="login-user text-light font-weight-bolder">
        Welcome {{ Auth::user()->name }} {{ Auth::user()->lastname }}!
      </div>
    </li>
    <li class="nav-item">
      <img 
        src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : asset('images/uploadprof.png') }}"
        class="img-circle elevation-2"
        alt="User Image"
        style="width:40px; height:40px; object-fit:cover;"
      />
    </li>
  </ul>
</nav>
