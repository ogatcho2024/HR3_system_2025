<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <title>Cali</title>
  <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Styles --}}
  @stack('styles')
</head>
<body>

  {{-- Main Page Content --}}
  @yield('content')

  {{-- Scripts --}}
  @stack('scripts')

  
</body>
</html>
