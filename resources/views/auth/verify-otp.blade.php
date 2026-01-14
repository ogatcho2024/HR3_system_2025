<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Two-Factor Authentication</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="w-full min-h-screen flex justify-center items-center bg-gradient-to-br from-blue-900 to-blue-950 p-4">

<div class="bg-white shadow-2xl rounded-lg px-8 pt-8 pb-8 w-full max-w-md">
    <div class="text-center mb-6">
        <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Two-Factor Authentication</h2>
        <p class="text-sm text-gray-600 mt-2">Enter the 6-digit code sent to your email</p>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            {{ session('info') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}" id="otpForm">
        @csrf
        <div class="mb-6">
            <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">Enter OTP Code</label>
            <input type="text" name="otp" id="otp" required maxlength="6" pattern="[0-9]{6}" 
                placeholder="000000"
                class="shadow appearance-none border-2 border-gray-300 rounded w-full py-3 px-4 text-gray-700 text-center text-2xl tracking-widest leading-tight focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
            @error('otp')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-center justify-center">
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition-colors">
                Verify and Continue
            </button>
        </div>
    </form>

    <!-- Resend Button -->
    <div class="text-center mt-6">
        <button id="resendBtn" onclick="resendOTP()" disabled
            class="bg-gray-300 text-gray-600 font-semibold py-2 px-6 rounded-lg cursor-not-allowed transition-colors">
            Resend OTP (<span id="timer">60</span>s)
        </button>
    </div>

    <!-- Resend Message -->
    <div id="resendMessage" class="text-center mt-3 text-sm font-medium text-green-600 hidden">✓ OTP has been resent to your email!</div>
    <div id="resendError" class="text-center mt-3 text-sm font-medium text-red-600 hidden">Failed to resend OTP. Please try again.</div>

    <!-- Back to Logout -->
    <div class="text-center mt-6 pt-4 border-t border-gray-200">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors">
                ← Back to Login
            </button>
        </form>
    </div>
</div>

<script>
    let timer = 60;
    const timerElement = document.getElementById('timer');
    const resendBtn = document.getElementById('resendBtn');
    const resendMsg = document.getElementById('resendMessage');
    const resendError = document.getElementById('resendError');
    const otpInput = document.getElementById('otp');

    // Auto-focus on OTP input
    otpInput.focus();

    // Only allow numbers in OTP input
    otpInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Start countdown timer
    const countdown = setInterval(() => {
        timer--;
        timerElement.textContent = timer;
        if (timer <= 0) {
            clearInterval(countdown);
            resendBtn.disabled = false;
            resendBtn.classList.remove('bg-gray-300', 'text-gray-600', 'cursor-not-allowed');
            resendBtn.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700', 'cursor-pointer');
            resendBtn.textContent = 'Resend OTP';
        }
    }, 1000);

    function resendOTP() {
        resendBtn.disabled = true;
        resendBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        resendBtn.classList.add('bg-gray-300', 'cursor-not-allowed');
        resendBtn.textContent = 'Sending...';
        resendMsg.classList.add('hidden');
        resendError.classList.add('hidden');

        fetch("{{ route('otp.resend') }}")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resendMsg.classList.remove('hidden');
                    timer = 60;
                    timerElement.textContent = timer;
                    resendBtn.textContent = `Resend OTP (${timer}s)`;

                    const newCountdown = setInterval(() => {
                        timer--;
                        timerElement.textContent = timer;
                        if (timer <= 0) {
                            clearInterval(newCountdown);
                            resendBtn.disabled = false;
                            resendBtn.classList.remove('bg-gray-300', 'cursor-not-allowed');
                            resendBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                            resendBtn.textContent = 'Resend OTP';
                        }
                    }, 1000);

                    // Hide success message after 3 seconds
                    setTimeout(() => {
                        resendMsg.classList.add('hidden');
                    }, 3000);
                } else {
                    resendError.textContent = data.message || 'Failed to resend OTP.';
                    resendError.classList.remove('hidden');
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('bg-gray-300', 'cursor-not-allowed');
                    resendBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    resendBtn.textContent = 'Resend OTP';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resendError.textContent = 'Network error. Please try again.';
                resendError.classList.remove('hidden');
                resendBtn.disabled = false;
                resendBtn.classList.remove('bg-gray-300', 'cursor-not-allowed');
                resendBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                resendBtn.textContent = 'Resend OTP';
            });
    }
</script>

</body>
</html>