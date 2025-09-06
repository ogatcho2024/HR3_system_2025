<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="relative min-h-screen overflow-hidden">
    <!-- Background Image Layer -->
    <div class="absolute inset-0 bg-[url('/images/login.jpg')] bg-cover bg-center filter blur-sm opacity-200 z-0"></div>

    <!-- Foreground Content -->
    <div class="relative z-10 flex items-center justify-center min-h-screen">
        <!-- Your content here -->
        <div class="bg-slate-200 p-8 rounded-lg shadow-lg max-w-md w-full text-center">
            <div class="flex justify-center mb-6">
                <svg class="w-16 h-16 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 22C6.48 22 2 17.52 2 12S6.48 2 12 2s10 4.48 10 10-4.48 10-10 10zM10 13.17l-2.12-2.12L6.47 12.46 10 16l7-7-1.41-1.41L10 13.17z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Success!</h2>
            <p class="text-gray-600 mb-6">
                You have successfully logged in / registered.
            </p>
            <a href="{{ route('login') }}"
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>
