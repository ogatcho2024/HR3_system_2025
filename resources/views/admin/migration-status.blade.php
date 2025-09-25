<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Migration Status - Login Rate Limiting</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">

    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Login Rate Limiting - Migration Status</h1>
            
            <!-- Status Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                    {{ session('info') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Table Status -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold mb-2">Database Table Status</h2>
                <div class="flex items-center gap-2">
                    @if($hasTable)
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-green-700 font-medium">login_attempts table exists ✓</span>
                        <span class="text-green-600">Rate limiting is active!</span>
                    @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-red-700 font-medium">login_attempts table missing ✗</span>
                        <span class="text-red-600">Rate limiting is disabled</span>
                    @endif
                </div>
            </div>

            @if(!$hasTable)
                <!-- Migration Form -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Action Required</h3>
                    <p class="text-yellow-700 mb-4">
                        The login_attempts table needs to be created to enable rate limiting protection. 
                        This will protect your application from brute force login attacks.
                    </p>
                    
                    <form method="POST" action="{{ route('admin.migration.create') }}" class="space-y-4">
                        @csrf
                        <div class="flex items-center">
                            <input type="checkbox" name="confirm" id="confirm" value="1" required
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="confirm" class="ml-2 text-sm text-gray-700">
                                I confirm that I want to create the login_attempts table
                            </label>
                        </div>
                        
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Create login_attempts Table
                        </button>
                    </form>
                </div>
            @endif

            <!-- Information -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">About Login Rate Limiting</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Blocks users after 3 failed login attempts</li>
                    <li>• 5-minute temporary lockout period</li>
                    <li>• Protects against brute force attacks</li>
                    <li>• Tracks both IP address and email</li>
                    <li>• Automatic cleanup of old records</li>
                </ul>
            </div>
            
            <div class="mt-6 text-center">
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>

</body>
</html>