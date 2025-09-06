<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <title>Login</title>
</head>
<body class="overflow-x-hidden">

<section class="relative w-full min-h-screen flex justify-center items-center p-4">
    <!-- Background -->
    <div class="absolute inset-0 bg-[url('/images/login.jpg')] bg-cover bg-center filter blur-sm opacity-200 z-0"></div>

    <!-- Main Container -->
    <div class="relative z-10 bg-slate-200 shadow-md w-full max-w-5xl min-h-96 rounded-lg flex flex-col md:flex-row overflow-hidden">
        
        <!-- Left Image Side -->
        <div class="w-full md:w-1/2 bg-white flex justify-center items-center p-4 md:rounded-l-lg">
            <img class="rounded-md w-full h-60 md:h-full object-cover shadow-md" src="{{ asset('images/login.jpg') }}" alt="Logo">
        </div>

        <!-- Right Form Side -->
        <div class="w-full md:w-1/2 p-6 flex flex-col justify-center">

            {{-- Show Google login error --}}
            @if ($errors->has('msg'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-xs text-center">
                    {{ $errors->first('msg') }}
                </div>
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

                <button type="submit"
                    class="w-full text-white bg-gray-900 hover:bg-gray-950 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 mt-3">
                    Sign In
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
