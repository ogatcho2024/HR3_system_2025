@extends('dashboard-user')

@section('title', 'QR Attendance Scanner')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">QR Attendance Scanner</h1>
            <p class="mt-2 text-sm text-gray-600">Scan employee QR codes to log attendance (TIME-IN / TIME-OUT)</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Scanner Card -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Camera Scanner</h2>
                        <p class="text-indigo-100 text-sm mt-1">Point camera at employee's QR code</p>
                    </div>
                    
                    <div class="p-6">
                        <!-- Camera Selection -->
                        <div class="mb-4">
                            <label for="cameraSelect" class="block text-sm font-medium text-gray-700 mb-2">Select Camera</label>
                            <select id="cameraSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Loading cameras...</option>
                            </select>
                        </div>

                        <!-- Scanner Container -->
                        <div id="reader" class="rounded-lg border-4 border-gray-200 overflow-hidden bg-black" style="width: 100%; max-width: 600px; height: 450px; margin: 0 auto; position: relative;"></div>
                        
                        <!-- Add CSS to ensure QR box visibility -->
                        <style>
                            /* Ensure QR scanning box is visible */
                            #reader video {
                                width: 100% !important;
                                height: 100% !important;
                                object-fit: cover !important;
                            }
                            
                            /* Make sure the scanning region outline is visible */
                            #reader canvas {
                                position: absolute !important;
                                top: 0 !important;
                                left: 0 !important;
                            }
                            
                            /* Highlight the QR shaded region */
                            #reader__dashboard_section_csr {
                                opacity: 0.5 !important;
                            }
                            
                            /* Ensure the scan region box is visible */
                            #qr-shaded-region {
                                border: 2px solid rgba(0, 255, 0, 0.5) !important;
                            }
                        </style>

                        <!-- Scanner Controls -->
                        <div class="mt-4 flex gap-3 justify-center">
                            <button id="startScanBtn" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow transition">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Start Scanner
                            </button>
                            <button id="stopScanBtn" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow transition hidden">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                                </svg>
                                Stop Scanner
                            </button>
                        </div>

                        <!-- Status Messages -->
                        <div id="statusMessage" class="mt-4 hidden">
                            <!-- Dynamic status messages will appear here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Scans Panel -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden sticky top-4">
                    <div class="bg-gray-800 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Recent Scans</h2>
                        <p class="text-gray-300 text-sm mt-1">Today's activity</p>
                    </div>
                    
                    <div class="p-4" style="max-height: 600px; overflow-y: auto;">
                        <div id="recentScans" class="space-y-3">
                            <div class="text-center py-8 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                                <p class="text-sm">No scans yet</p>
                                <p class="text-xs mt-1">Start scanning to see activity</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Scanner Instructions</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Click "Start Scanner" and allow camera access</li>
                            <li>Employee should display their daily QR code from "My QR Today" page</li>
                            <li>Position the QR code within the camera frame</li>
                            <li>Scanner will automatically detect and process the QR code</li>
                            <li>First scan = TIME-IN, Second scan = TIME-OUT</li>
                            <li>5-minute cooldown enforced between scans</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<!-- Audio for scan feedback -->
<audio id="successSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjGH0fPTgjMGHm7A7+OZUQ4PVK3n77BfHAc9ktjyz4A1Bjh+zPLaizsIGGS56+SdUhENTqfk8bllHgY7k9fzzYQ5CDiEzvPajj0HHnHD8OKcUg8NVq/o8bBfHAc/ltjyz4I1BjiBzfLajj0HH3HE8OSZUQ8PVK7o8bJiHQZAl9nz0II3Bjh+zPLajj0HHnDD8OScUhANVa/o8bFfHAc/ltjyz4I1BjiBzPLajj0HHnHE8OWbURAPVK7n8bBfHAc/ltjyz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bFfHQdAl9jzz4M1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4M1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQc/ltjyz4I1BjiBzPLajj0HHnHE8OWbURAPVK7o8bBfHQdAl9jzz4M1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQc/ltjyz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4M1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQc=" type="audio/wav">
</audio>

<script>
    let html5QrCode = null;
    let isScanning = false;
    let lastScanTime = 0;
    const SCAN_COOLDOWN = 3000; // 3 seconds cooldown between scans
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Initialize camera select
    async function initializeCameras() {
        try {
            const devices = await Html5Qrcode.getCameras();
            const select = document.getElementById('cameraSelect');
            select.innerHTML = '';
            
            if (devices && devices.length > 0) {
                devices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.id;
                    option.text = device.label || `Camera ${index + 1}`;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No cameras found</option>';
            }
        } catch (err) {
            console.error('Error getting cameras:', err);
            showError('Unable to access cameras. Please check permissions.');
        }
    }

    // Start scanning
    async function startScanning() {
        const cameraId = document.getElementById('cameraSelect').value;
        
        if (!cameraId) {
            showError('Please select a camera first');
            return;
        }

        try {
            html5QrCode = new Html5Qrcode("reader");
            
            await html5QrCode.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: function(viewfinderWidth, viewfinderHeight) {
                        // Calculate QR box size - 70% of the smaller dimension
                        let minEdgePercentage = 0.7;
                        let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                        let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                        return {
                            width: qrboxSize,
                            height: qrboxSize
                        };
                    },
                    aspectRatio: 1.0,
                    showTorchButtonIfSupported: true,
                    formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
                },
                onScanSuccess,
                onScanError
            );
            
            isScanning = true;
            document.getElementById('startScanBtn').classList.add('hidden');
            document.getElementById('stopScanBtn').classList.remove('hidden');
            document.getElementById('cameraSelect').disabled = true;
            showInfo('Scanner active - Ready to scan QR codes');
        } catch (err) {
            console.error('Error starting scanner:', err);
            showError('Failed to start scanner: ' + err);
        }
    }

    // Stop scanning
    async function stopScanning() {
        if (html5QrCode && isScanning) {
            try {
                await html5QrCode.stop();
                html5QrCode.clear();
                html5QrCode = null;
                isScanning = false;
                document.getElementById('startScanBtn').classList.remove('hidden');
                document.getElementById('stopScanBtn').classList.add('hidden');
                document.getElementById('cameraSelect').disabled = false;
                showInfo('Scanner stopped');
            } catch (err) {
                console.error('Error stopping scanner:', err);
            }
        }
    }

    // Handle successful QR code scan
    function onScanSuccess(decodedText, decodedResult) {
        const now = Date.now();
        
        // Prevent rapid scanning
        if (now - lastScanTime < SCAN_COOLDOWN) {
            return;
        }
        lastScanTime = now;

        // Play success sound
        const audio = document.getElementById('successSound');
        audio.play().catch(() => {});

        // Parse QR code data
        try {
            const qrData = JSON.parse(decodedText);
            processAttendance(qrData);
        } catch (err) {
            showError('Invalid QR code format');
        }
    }

    // Handle scan errors (silent)
    function onScanError(errorMessage) {
        // Ignore scan errors - they're normal when no QR code is visible
    }

    // Process attendance via API
    async function processAttendance(qrData) {
        showInfo('Processing...');

        try {
            const response = await fetch('{{ route("attendance.qr-scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(qrData)
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(`✓ ${result.employee.name} - ${result.type} at ${result.time}`);
                addRecentScan(result);
            } else {
                showError(result.message || 'Scan failed');
            }
        } catch (err) {
            console.error('Error processing attendance:', err);
            showError('Network error - please try again');
        }
    }

    // Add scan to recent list
    function addRecentScan(result) {
        const container = document.getElementById('recentScans');
        
        // Remove "no scans" message
        if (container.querySelector('.text-gray-400')) {
            container.innerHTML = '';
        }

        const scanItem = document.createElement('div');
        scanItem.className = `p-3 rounded-lg border-l-4 ${result.type === 'IN' ? 'bg-green-50 border-green-500' : 'bg-blue-50 border-blue-500'}`;
        scanItem.innerHTML = `
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">${result.employee.name}</p>
                    <p class="text-xs text-gray-600">ID: ${result.employee.employee_id}</p>
                </div>
                <span class="px-2 py-1 rounded text-xs font-bold ${result.type === 'IN' ? 'bg-green-600 text-white' : 'bg-blue-600 text-white'}">
                    ${result.type}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">${result.time}</p>
        `;

        container.insertBefore(scanItem, container.firstChild);

        // Limit to 20 recent scans
        while (container.children.length > 20) {
            container.removeChild(container.lastChild);
        }
    }

    // Show status messages
    function showMessage(message, type) {
        const statusDiv = document.getElementById('statusMessage');
        statusDiv.className = `mt-4 p-4 rounded-lg ${
            type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' :
            type === 'error' ? 'bg-red-50 border border-red-200 text-red-800' :
            'bg-blue-50 border border-blue-200 text-blue-800'
        }`;
        statusDiv.textContent = message;
        statusDiv.classList.remove('hidden');

        // Auto-hide after 5 seconds
        setTimeout(() => {
            statusDiv.classList.add('hidden');
        }, 5000);
    }

    function showSuccess(message) { showMessage(message, 'success'); }
    function showError(message) { showMessage(message, 'error'); }
    function showInfo(message) { showMessage(message, 'info'); }

    // Event listeners
    document.getElementById('startScanBtn').addEventListener('click', startScanning);
    document.getElementById('stopScanBtn').addEventListener('click', stopScanning);

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        initializeCameras();
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (isScanning) {
            stopScanning();
        }
    });
</script>
@endsection
