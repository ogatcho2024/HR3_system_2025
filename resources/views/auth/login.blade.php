<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <title>Login</title>
</head>
<body class="overflow-x-hidden">

<section class="relative w-full min-h-screen flex justify-center items-center p-4">
    <!-- Background -->
    <div class="absolute inset-0 bg-[url('images/login2.jpg')] bg-cover bg-center filter z-0"></div>

    <!-- Main Container -->
    <div class="relative z-10 bg-slate-200/50 shadow-md w-full max-w-5xl min-h-96 rounded-lg flex flex-col md:flex-row overflow-hidden">
        
        <!-- Left Image Side -->
        <div class="w-full md:w-1/2 bg-white flex bg-white/30 backdrop-blur-md justify-center items-center p-4 md:rounded-l-lg">
            <img class="rounded-md w-full h-60 md:h-full object-cover shadow-md" src="{{ asset('images/logo2.png') }}" alt="Logo">
        </div>

        <!-- Right Form Side -->
        <div class="w-full md:w-1/2 p-6 flex bg-white/30 backdrop-blur-md flex-col justify-center">

            {{-- Show Google login error --}}
            @if ($errors->has('msg'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-xs text-center">
                    {{ $errors->first('msg') }}
                </div>
            @endif

            {{-- Show rate limiting error --}}
            @if ($errors->has('throttle'))
                <div id="throttleError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-xs text-center">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span id="throttleMessage">Too many login attempts. Please try again in <strong id="countdown">5</strong> minutes.</span>
                    </div>
                    <div class="mt-2 text-xs opacity-75">
                        Time remaining: <span id="timeDisplay" class="font-mono">05:00</span>
                    </div>
                </div>

                <script>
                let countdownSeconds = 300; // Default 5 minutes in seconds
                
                // Get actual remaining time from server
                fetch('{{ route('login.block-time') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: '{{ old('email') }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.blocked) {
                        countdownSeconds = Math.max(1, data.seconds_remaining);
                        // Disable login button
                        document.getElementById('loginButton').disabled = true;
                        document.getElementById('loginButtonText').textContent = 'Account Locked';
                        updateCountdown();
                    } else {
                        // Not blocked, hide error
                        document.getElementById('throttleError').style.display = 'none';
                        // Enable login button
                        document.getElementById('loginButton').disabled = false;
                        document.getElementById('loginButtonText').textContent = 'Sign In';
                    }
                })
                .catch(error => {
                    console.log('Error checking block time, using default countdown');
                    updateCountdown();
                });
                
                function updateCountdown() {
                    if (countdownSeconds <= 0) {
                        document.getElementById('throttleError').style.display = 'none';
                        // Re-enable login button
                        document.getElementById('loginButton').disabled = false;
                        document.getElementById('loginButtonText').textContent = 'Sign In';
                        return;
                    }
                    
                    const minutes = Math.floor(countdownSeconds / 60);
                    const seconds = countdownSeconds % 60;
                    
                    document.getElementById('countdown').textContent = Math.max(1, minutes);
                    document.getElementById('timeDisplay').textContent = 
                        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                    
                    countdownSeconds--;
                    setTimeout(updateCountdown, 1000);
                }
                </script>
            @endif

            @if(session('acc_banned'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-xs text-center">Your account has been banned.</div>
            @endif

            @if(session('success'))
                <div id="successMessage" class="mb-4 p-3 bg-green-100 text-green-800 text-sm rounded-md text-center">
                    {{ session('success') }}
                </div>

                <script>
                    setTimeout(() => {
                        const msg = document.getElementById('successMessage');
                        if (msg) msg.style.display = 'none';
                    }, 4000);
                </script>
            @endif


            <div class="text-center mb-4">
                <h1 class="font-bold text-xl md:text-2xl text-blue-950">Welcome Back to CaliCrane!</h1>
                <p class="opacity-50 font-bold text-xs text-gray-900 mt-2">Sign in your account</p>
            </div>

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-3 mt-3 w-full">
                @csrf

                <!-- Email -->
                <div class="relative z-0 w-full group">
                    <input type="email" name="email" id="email"
                        class="block py-2 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 focus:outline-none focus:ring-0 focus:border-black peer"
                        placeholder=" " value="{{ old('email') }}" required />
                    <label for="email"
                        class="absolute text-xs text-blue-950 duration-300 transform scale-75 -translate-y-6 top-3 origin-[0] peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:scale-75 peer-focus:-translate-y-6">
                        Email address
                    </label>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="relative z-0 w-full group">
                    <input type="password" name="password" id="password"
                        class="block py-2 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 focus:outline-none focus:ring-0 focus:border-black peer"
                        placeholder=" " required />
                    <label for="password"
                        class="absolute text-xs text-blue-950 duration-300 transform scale-75 -translate-y-6 top-3 origin-[0] peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:scale-75 peer-focus:-translate-y-6">
                        Password
                    </label>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs mt-1">
                    <label class="flex items-center gap-1 text-gray-900">
                        <input type="checkbox" name="remember" class="h-3 w-3 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="opacity-60 text-gray-900 font-semibold">Forgot Password?</a>
                </div>

                <button type="submit" id="loginButton"
                    class="w-full text-white bg-gray-900 hover:bg-gray-950 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 mt-3 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="loginButtonText">Sign In</span>
                </button>

                <div class="flex items-center my-4">
                    <div class="flex-grow border-t border-gray-300"></div>
                    <span class="mx-3 text-xs font-bold text-gray-400">OR</span>
                    <div class="flex-grow border-t border-gray-300"></div>
                </div>

                <!-- Social Logins -->
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <a href="{{ route('google.login') }}"
                        class="flex items-center justify-center gap-2 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-100 text-xs w-full">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
                        Google
                    </a>
                    <button type="button"
                        class="flex items-center justify-center gap-2 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-100 text-xs w-full">
                        <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" class="w-5 h-5" alt="Facebook">
                        Facebook
                    </button>
                </div>

                <!-- Register -->
                <div class="text-center text-xs text-gray-900 font-bold mt-4">
                    <span class="opacity-50">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-blue-600 hover:underline ml-1">Register</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>
</html>
