<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <title>Register</title>
    <style>
        body {
            overflow-x: hidden;
        }
    </style>
</head>
<body>
    <section class="relative w-full min-h-screen overflow-hidden flex flex-col justify-center items-center p-4">
        <!-- ✅ Background Layer -->
        <div class="absolute inset-0 bg-[url('/images/login.jpg')] bg-cover bg-center filter blur-sm opacity-200 z-0"></div>
        <!-- ✅ Foreground Content -->
        <div class="relative z-10 bg-slate-200 shadow-md w-full max-w-4xl min-h-96 rounded-lg flex flex-col md:flex-row overflow-hidden">
            <!-- Left side -->
            <div class="w-full md:w-1/2 bg-white flex justify-center items-center p-4 rounded-t-lg md:rounded-l-lg md:rounded-tr-none">
                <img class="rounded-md w-full h-full shadow-md object-cover" src="{{ asset('images/login.jpg') }}" alt="Logo">
            </div>

            <!-- Right side -->
            <div class="w-full md:w-1/2 p-6 flex flex-col justify-center">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-xs text-center">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif


                <div class="text-center mb-4">
                    <h1 class="font-bold text-2xl text-blue-950">Sign Up to CaliCrane!</h1>
                    <p class="opacity-50 font-bold text-xs text-gray-900 mt-3">Sign up for new account</p>
                </div>

                <form method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data" class="p-4 w-full">
                    @csrf

                    <div class="w-full justify-center items-center flex mb-6">
                        <div class="w-25 h-25 rounded-full relative">
                            <img id="profilePreview" class="w-full h-full object-cover rounded-full shadow-lg relative" src="{{ asset('images/uploadprof.png') }}" alt="Default Profile Preview">
                            <input class="hidden" id="inputFile" name="photo" type="file" accept="image/*">
                            <label for="inputFile" class="bg-blue-950 w-8 h-8 absolute bottom-[-8px] right-1 z-50 cursor-pointer rounded-full shadow-lg hover:scale-100 flex justify-center items-center">
                                <svg class="w-5 h-5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3 3H0V14H16V3H13L11 1H5L3 3ZM8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11Z"
                                        fill="#ffffff" />
                                </svg>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="name" id="name"
                                class="block py-2.5 px-0 w-full text-xs text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-black peer"
                                placeholder=" " required />
                            <label for="name"
                                class="peer-focus:font-medium absolute text-xs text-blue-950 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-950 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                First Name
                            </label>
                        </div>

                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="lastname" id="lastname"
                                class="block py-2.5 px-0 w-full text-xs text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-black peer"
                                placeholder=" " required />
                            <label for="lastname"
                                class="peer-focus:font-medium absolute text-xs text-blue-950 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-950 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                Last Name
                            </label>
                        </div>

                        <div class="relative z-0 w-full mb-5 group">
                            <input type="email" name="email" id="email"
                                class="block py-2.5 px-0 w-full text-xs text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-black peer"
                                placeholder=" " required />
                            <label for="email"
                                class="peer-focus:font-medium absolute text-xs text-blue-950 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-950 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                Email Address
                            </label>
                        </div>

                        <div class="relative z-0 w-full mb-5 group">
                            <input type="tel" name="phone" id="phone"
                                class="block py-2.5 px-0 w-full text-xs text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-black peer"
                                placeholder=" " required />
                            <label for="phone"
                                class="peer-focus:font-medium absolute text-xs text-blue-950 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-950 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                Phone Number
                            </label>
                        </div>

                        <div class="relative z-0 w-full mb-5 group">
                            <input type="password" name="password" id="password"
                                class="block py-2.5 px-0 w-full text-xs text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-black peer"
                                placeholder=" " required />
                            <label for="password"
                                class="peer-focus:font-medium absolute text-xs text-blue-950 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-950 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                Password
                            </label>
                            
                            <!-- Password Strength Indicator -->
                            <div id="passwordStrength" class="mt-2 hidden">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div id="strengthBar" class="h-2 rounded-full transition-all duration-300" style="width: 0%;"></div>
                                    </div>
                                    <span id="strengthText" class="text-xs font-medium">Very Weak</span>
                                </div>
                            </div>
                            
                            <!-- Password Requirements -->
                            <div id="passwordRequirements" class="mt-2 text-xs text-gray-600 space-y-1 hidden">
                                <div class="grid grid-cols-2 gap-2">
                                    <div id="req-length" class="flex items-center gap-1">
                                        <span class="w-4 h-4 rounded-full bg-gray-300 flex items-center justify-center text-white text-xs">✗</span>
                                        <span>8+ characters</span>
                                    </div>
                                    <div id="req-uppercase" class="flex items-center gap-1">
                                        <span class="w-4 h-4 rounded-full bg-gray-300 flex items-center justify-center text-white text-xs">✗</span>
                                        <span>Uppercase letter</span>
                                    </div>
                                    <div id="req-lowercase" class="flex items-center gap-1">
                                        <span class="w-4 h-4 rounded-full bg-gray-300 flex items-center justify-center text-white text-xs">✗</span>
                                        <span>Lowercase letter</span>
                                    </div>
                                    <div id="req-number" class="flex items-center gap-1">
                                        <span class="w-4 h-4 rounded-full bg-gray-300 flex items-center justify-center text-white text-xs">✗</span>
                                        <span>Number</span>
                                    </div>
                                    <div id="req-special" class="flex items-center gap-1" style="grid-column: 1 / -1;">
                                        <span class="w-4 h-4 rounded-full bg-gray-300 flex items-center justify-center text-white text-xs">✗</span>
                                        <span>Special character (!@#$%^&*)</span>
                                    </div>
                                </div>
                            </div>
                            
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="position" id="position"
                                class="block py-2.5 px-0 w-full text-xs text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-black peer"
                                placeholder=" " required />
                            <label for="position"
                                class="peer-focus:font-medium absolute text-xs text-blue-950 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-950 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                Position
                            </label>
                        </div>

                        <div class="relative z-0 w-full mb-5 group">
                            <select name="account_type" id="account_type" class="block py-2.5 px-0 w-full text-xs text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-black peer" required>
                                <option value="" disabled selected></option>
                                @foreach($accountTypes ?? [] as $type)
                                    <option value="{{ $type }}" {{ old('account_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            <label for="account_type" class="peer-focus:font-medium absolute text-sm text-blue-950 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-950 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Account Type</label>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-center mb-4 text-xs text-gray-700">
                        <input id="terms" type="checkbox" required class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <label for="terms" class="ml-2">
                            I agree to the 
                            <button type="button" data-modal-target="termsModal" data-modal-toggle="termsModal" class="text-blue-600 hover:underline">
                                Terms and Conditions
                            </button>
                        </label>
                    </div>

                    <button type="submit" class="w-full text-white bg-gray-900 hover:bg-gray-950 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5">
                        Sign - Up
                    </button>

                    <div class="text-center text-[10px] text-gray-900 font-bold mt-6">
                        <span class="opacity-50">Already have an account? </span>
                        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login Here</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Terms Modal -->
    <div id="termsModal" tabindex="-1" aria-hidden="true" class="hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50">
        <div class="relative p-4 w-full max-w-sm sm:max-w-md max-h-full">
            <div class="relative bg-slate-200 rounded-lg shadow">
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-lg font-semibold text-gray-900">Terms and Conditions</h3>
                    <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="termsModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <div class="p-4 space-y-4 text-sm text-gray-700">
                    <p>By registering, you agree to CaliCrane’s Terms and Conditions. Your information will be securely stored and will only be used for internal purposes.</p>
                    <p>Please read everything carefully before proceeding with account creation.</p>
                </div>
                <div class="flex justify-end items-center p-4 border-t border-gray-200 rounded-b">
                    <button data-modal-hide="termsModal" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputFile = document.getElementById('inputFile');
            const profilePreview = document.getElementById('profilePreview');
            const passwordInput = document.getElementById('password');
            const strengthIndicator = document.getElementById('passwordStrength');
            const requirementsIndicator = document.getElementById('passwordRequirements');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            // File upload handling
            inputFile.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    if (!file.type.match('image.*')) {
                        alert('Please select an image file');
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Image size should be less than 5MB');
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        profilePreview.src = e.target.result;
                        profilePreview.style.transition = 'transform 0.3s ease';
                        profilePreview.style.transform = 'scale(1.05)';
                        setTimeout(() => {
                            profilePreview.style.transform = 'scale(1)';
                        }, 300);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Password strength validation
            passwordInput.addEventListener('input', function () {
                const password = this.value;
                
                if (password.length > 0) {
                    strengthIndicator.classList.remove('hidden');
                    requirementsIndicator.classList.remove('hidden');
                    checkPasswordStrength(password);
                } else {
                    strengthIndicator.classList.add('hidden');
                    requirementsIndicator.classList.add('hidden');
                }
            });

            function checkPasswordStrength(password) {
                let score = 0;
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password)
                };

                // Calculate score
                if (requirements.length) score++;
                if (requirements.uppercase) score++;
                if (requirements.lowercase) score++;
                if (requirements.number) score++;
                if (requirements.special) score++;
                if (password.length >= 12) score++; // Bonus for longer passwords

                // Update requirements indicators
                updateRequirement('req-length', requirements.length);
                updateRequirement('req-uppercase', requirements.uppercase);
                updateRequirement('req-lowercase', requirements.lowercase);
                updateRequirement('req-number', requirements.number);
                updateRequirement('req-special', requirements.special);

                // Update strength bar and text
                updateStrengthIndicator(Math.min(score, 5));
            }

            function updateRequirement(elementId, met) {
                const element = document.getElementById(elementId);
                const icon = element.querySelector('span');
                
                if (met) {
                    icon.classList.remove('bg-gray-300');
                    icon.classList.add('bg-green-500');
                    icon.textContent = '✓';
                    element.classList.remove('text-gray-600');
                    element.classList.add('text-green-600');
                } else {
                    icon.classList.remove('bg-green-500');
                    icon.classList.add('bg-gray-300');
                    icon.textContent = '✗';
                    element.classList.remove('text-green-600');
                    element.classList.add('text-gray-600');
                }
            }

            function updateStrengthIndicator(score) {
                const colors = {
                    0: { bg: 'bg-red-500', text: 'Very Weak', width: '20%' },
                    1: { bg: 'bg-red-400', text: 'Very Weak', width: '20%' },
                    2: { bg: 'bg-orange-500', text: 'Weak', width: '40%' },
                    3: { bg: 'bg-yellow-500', text: 'Fair', width: '60%' },
                    4: { bg: 'bg-blue-500', text: 'Good', width: '80%' },
                    5: { bg: 'bg-green-500', text: 'Strong', width: '100%' }
                };

                const config = colors[score] || colors[0];
                
                strengthBar.className = `h-2 rounded-full transition-all duration-300 ${config.bg}`;
                strengthBar.style.width = config.width;
                strengthText.textContent = config.text;
                strengthText.className = `text-xs font-medium ${config.bg.replace('bg-', 'text-')}`;
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>
</html>
