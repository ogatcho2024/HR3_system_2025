@extends('dashboard')

@section('title', 'Employee Portal')

@section('content')
<section class="relative w-full min-h-screen flex justify-center items-center p-4 bg-gradient-to-b from-slate-100 to-slate-200">

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-5xl min-h-96 rounded-2xl border border-slate-200 bg-white shadow-lg overflow-hidden flex flex-col md:flex-row">

        <!-- Left Info Side -->
        <div class="hidden md:flex md:w-1/2 p-10 bg-slate-900 text-white flex-col justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs tracking-wide">
                    Employee Self Service
                </div>
                <h2 class="mt-6 text-3xl font-semibold">HR3 Employee Portal</h2>
                <p class="mt-3 text-sm text-slate-200 leading-relaxed">
                    Secure access to attendance, shift requests, leave tracking, and notifications in one place.
                </p>
            </div>
            <div class="space-y-3 text-sm text-slate-200">
                <div class="flex items-start gap-2">
                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span>OTP‑secured sign-in with account lockout protection.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="mt-1 h-2 w-2 rounded-full bg-blue-400"></span>
                    <span>Real‑time updates for attendance and approvals.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="mt-1 h-2 w-2 rounded-full bg-amber-400"></span>
                    <span>Centralized alerts and audit visibility.</span>
                </div>
            </div>
        </div>
        
        <!-- Right Form Side -->
        <div class="w-full md:w-1/2 p-8 md:p-10 flex bg-white flex-col justify-center">

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


            <div class="text-center mb-6">
                <h1 class="font-semibold text-2xl text-slate-900">Welcome back</h1>
                <p class="text-sm text-slate-500 mt-2">Sign in to continue to your portal</p>
            </div>

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4 w-full">
                @csrf

                <!-- Email -->
                <div class="relative z-0 w-full group">
                    <input type="email" name="email" id="email"
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-transparent shadow-sm focus:border-slate-900 focus:ring-0 peer"
                        placeholder=" " value="{{ old('email') }}" required />
                    <label for="email"
                        class="absolute left-3 top-2 text-xs text-slate-600 transition-all duration-200 transform -translate-y-5 scale-90 bg-white px-1 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-5 peer-focus:scale-90">
                        Email address
                    </label>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="relative z-0 w-full group">
                    <input type="password" name="password" id="password"
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-transparent shadow-sm focus:border-slate-900 focus:ring-0 peer"
                        placeholder=" " required />
                    <label for="password"
                        class="absolute left-3 top-2 text-xs text-slate-600 transition-all duration-200 transform -translate-y-5 scale-90 bg-white px-1 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-5 peer-focus:scale-90">
                        Password
                    </label>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs mt-1">
                    <label class="flex items-center gap-2 text-slate-700">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="text-slate-500 hover:text-slate-900 font-medium">Forgot Password?</a>
                </div>

                <button type="submit" id="loginButton"
                    class="w-full text-white bg-slate-900 hover:bg-slate-950 focus:ring-4 focus:ring-slate-300 font-medium rounded-lg text-sm px-5 py-2.5 mt-2 disabled:opacity-50 disabled:cursor-not-allowed">
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
                        class="flex items-center justify-center gap-2 border border-slate-200 px-4 py-2 rounded-md hover:bg-slate-50 text-xs w-full">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
                        Google
                    </a>
                    <button type="button"
                        class="flex items-center justify-center gap-2 border border-slate-200 px-4 py-2 rounded-md hover:bg-slate-50 text-xs w-full">
                        <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" class="w-5 h-5" alt="Facebook">
                        Facebook
                    </button>
                </div>

                <!-- Register -->
                <div class="text-center text-xs text-slate-700 font-medium mt-4">
                    <span class="text-slate-500">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-slate-900 hover:underline ml-1">Register</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
@endsection
