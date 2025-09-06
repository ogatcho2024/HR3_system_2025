<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="w-full min-h-screen flex justify-center items-center bg-blue-950 p-4">

<div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-center">Enter OTP</h2>

    @if (session('success'))
        <p class="text-green-600">{{ session('success') }}</p>
    @elseif (session('error'))
        <p class="text-red-600">{{ session('error') }}</p>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <div class="mb-4">
            <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">One-Time Password</label>
            <input type="text" name="otp" id="otp" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="flex items-center justify-between">
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Verify
            </button>
        </div>
    </form>

    <!-- Resend Button -->
    <div class="text-center mt-4">
        <button id="resendBtn" onclick="resendOTP()" disabled
            class="bg-gray-400 text-white font-bold py-2 px-4 rounded cursor-not-allowed">
            Resend OTP (<span id="timer">30</span>s)
        </button>
    </div>

    <!-- Resend Message -->
    <div id="resendMessage" class="text-center mt-2 text-sm text-green-600 hidden">OTP has been resent!</div>

    <!-- Back to Logout -->
    <div class="text-center mt-4">
        <a href="{{ route('logout') }}"
            class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Back to Login
        </a>
    </div>
</div>

<script>
    let timer = 30;
    const timerElement = document.getElementById('timer');
    const resendBtn = document.getElementById('resendBtn');
    const resendMsg = document.getElementById('resendMessage');

    const countdown = setInterval(() => {
        timer--;
        timerElement.textContent = timer;
        if (timer <= 0) {
            clearInterval(countdown);
            resendBtn.disabled = false;
            resendBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            resendBtn.classList.add('bg-blue-500', 'hover:bg-blue-700');
            resendBtn.textContent = 'Resend OTP';
        }
    }, 1000);

    function resendOTP() {
        resendBtn.disabled = true;
        resendBtn.textContent = 'Sending...';
        resendMsg.classList.add('hidden');

        fetch("{{ route('otp.resend') }}")
            .then(response => response.text())
            .then(data => {
                resendBtn.textContent = 'Resend OTP (30s)';
                resendBtn.disabled = true;
                resendBtn.classList.remove('bg-blue-500', 'hover:bg-blue-700');
                resendBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                resendMsg.classList.remove('hidden');
                timer = 30;
                timerElement.textContent = timer;

                const newCountdown = setInterval(() => {
                    timer--;
                    timerElement.textContent = timer;
                    if (timer <= 0) {
                        clearInterval(newCountdown);
                        resendBtn.disabled = false;
                        resendBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                        resendBtn.classList.add('bg-blue-500', 'hover:bg-blue-700');
                        resendBtn.textContent = 'Resend OTP';
                    }
                }, 1000);
            })
            .catch(error => {
                alert('Failed to resend OTP.');
                console.error(error);
            });
    }
</script>

</body>
</html>