@extends('dashboard-user')

@section('title', 'My QR Code Today')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My QR Code for Today</h1>
            <p class="mt-2 text-sm text-gray-600">Scan this QR code at the attendance scanner to log your TIME-IN and TIME-OUT</p>
        </div>

        <!-- QR Code Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Daily Attendance QR Code</h2>
                <p class="text-black-100 font-bold text-sm mt-1">Valid for: {{ \Carbon\Carbon::parse($currentDate)->format('l, F j, Y') }}</p>
            </div>
            
            <div class="p-8">
                <div class="flex flex-col md:flex-row items-center justify-center gap-8">
                    <!-- QR Code Display -->
                    <div class="flex flex-col items-center">
                        <div id="qrcode" class="bg-white p-4 rounded-lg border-4 border-indigo-200 shadow-md"></div>
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-600">Scan this code at the attendance scanner</p>
                            <p class="text-xs text-gray-500 mt-1">Auto-refreshes at midnight</p>
                        </div>
                    </div>

                    <!-- Employee Info -->
                    <div class="flex-1 space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Employee Information</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Name:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $user->name }} {{ $user->lastname }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Employee ID:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $employee->employee_id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Department:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $employee->department ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Position:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $employee->position ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Status -->
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <h3 class="text-sm font-semibold text-blue-900 mb-3">Today's Attendance</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Status:</span>
                                    <span class="text-sm font-medium text-blue-900">{{ $stats['status'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Time IN:</span>
                                    <span class="text-sm font-medium text-blue-900">{{ $stats['time_in'] ?? '--:--' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Time OUT:</span>
                                    <span class="text-sm font-medium text-blue-900">{{ $stats['time_out'] ?? '--:--' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Scans Today:</span>
                                    <span class="text-sm font-medium text-blue-900">{{ $stats['total_logs'] }}/2</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notice -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Important Security Notice</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Valid for TODAY only</strong> - This QR code expires at midnight</li>
                            <li><strong>Do NOT share</strong> this QR code with anyone</li>
                            <li>Maximum of <strong>2 scans per day</strong> (IN and OUT)</li>
                            <li><strong>5-minute cooldown</strong> between scans</li>
                            <li>Only scan at authorized attendance scanners</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">How to Use</h3>
            <ol class="space-y-3">
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3">1</span>
                    <span class="text-gray-700">Show this QR code to the attendance scanner (operated by Admin/Staff)</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3">2</span>
                    <span class="text-gray-700">Your first scan of the day will be recorded as <strong>TIME-IN</strong></span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3">3</span>
                    <span class="text-gray-700">Your second scan will be recorded as <strong>TIME-OUT</strong></span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3">4</span>
                    <span class="text-gray-700">Wait at least 5 minutes between scans if you need to rescan</span>
                </li>
            </ol>
        </div>

        <!-- Back Button -->
        <div class="mt-6 text-center">
            <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<script>
    // QR Code payload from server
    const qrPayload = {!! json_encode($qrPayload) !!};
    
    // Generate QR Code
    function generateQRCode() {
        // Clear existing QR code
        document.getElementById('qrcode').innerHTML = '';
        
        // Generate new QR code
        new QRCode(document.getElementById('qrcode'), {
            text: qrPayload,
            width: 256,
            height: 256,
            colorDark: '#4F46E5',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }
    
    // Generate QR code on page load
    generateQRCode();
    
    // Calculate time until midnight for auto-refresh
    function getTimeUntilMidnight() {
        const now = new Date();
        const midnight = new Date();
        midnight.setHours(24, 0, 0, 0);
        return midnight - now;
    }
    
    // Auto-refresh page at midnight
    setTimeout(function() {
        location.reload();
    }, getTimeUntilMidnight());
    
    // Show countdown timer (optional)
    function updateCountdown() {
        const timeUntilMidnight = getTimeUntilMidnight();
        const hours = Math.floor(timeUntilMidnight / (1000 * 60 * 60));
        const minutes = Math.floor((timeUntilMidnight % (1000 * 60 * 60)) / (1000 * 60));
        
        console.log(`QR code will refresh in ${hours}h ${minutes}m`);
    }
    
    // Update countdown every minute
    setInterval(updateCountdown, 60000);
</script>
@endsection
