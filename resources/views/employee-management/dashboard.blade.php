@extends('dashboard')

@section('title', 'Employee Management Dashboard')

@section('content')
<div class="min-h-screen bg-gray-300">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            @include('partials.breadcrumbs', ['breadcrumbs' => [
                ['label' => 'Employee Self Service Management', 'url' => route('employee-management.dashboard')]
            ]])
            
            

            <!-- Stats Card Boxes -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Pending Leave Requests Card -->
                <div class="bg-gradient-to-r from-blue-400 to-blue-500 overflow-hidden shadow-lg rounded-2xl border-l-8 border-blue-600 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <div class="p-6 bg-white">
                        <div class="flex items-center justify-center">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-15 h-15 bg-blue-500 rounded-full flex items-center justify-center shadow-inner">
                                    <svg fill="#000000ff" viewBox="-4 -4 25 25" id="request-send-16px" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                        <path id="Path_44" data-name="Path 44" d="M-18,11a2,2,0,0,0,2-2,2,2,0,0,0-2-2,2,2,0,0,0-2,2A2,2,0,0,0-18,11Zm0-3a1,1,0,0,1,1,1,1,1,0,0,1-1,1,1,1,0,0,1-1-1A1,1,0,0,1-18,8Zm2.5,4h-5A2.5,2.5,0,0,0-23,14.5,1.5,1.5,0,0,0-21.5,16h7A1.5,1.5,0,0,0-13,14.5,2.5,2.5,0,0,0-15.5,12Zm1,3h-7a.5.5,0,0,1-.5-.5A1.5,1.5,0,0,1-20.5,13h5A1.5,1.5,0,0,1-14,14.5.5.5,0,0,1-14.5,15ZM-7,2.5v5A2.5,2.5,0,0,1-9.5,10h-2.793l-1.853,1.854A.5.5,0,0,1-14.5,12a.493.493,0,0,1-.191-.038A.5.5,0,0,1-15,11.5v-2a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v.793l1.146-1.147A.5.5,0,0,1-12.5,9h3A1.5,1.5,0,0,0-8,7.5v-5A1.5,1.5,0,0,0-9.5,1h-7A1.5,1.5,0,0,0-18,2.5v3a.5.5,0,0,1-.5.5.5.5,0,0,1-.5-.5v-3A2.5,2.5,0,0,1-16.5,0h7A2.5,2.5,0,0,1-7,2.5Zm-7.854,3.646L-12.707,4H-14.5a.5.5,0,0,1-.5-.5.5.5,0,0,1,.5-.5h3a.5.5,0,0,1,.191.038.506.506,0,0,1,.271.271A.5.5,0,0,1-11,3.5v3a.5.5,0,0,1-.5.5.5.5,0,0,1-.5-.5V4.707l-2.146,2.147A.5.5,0,0,1-14.5,7a.5.5,0,0,1-.354-.146A.5.5,0,0,1-14.854,6.146Z" transform="translate(23)"></path> </g>
                                    </svg>
                                </div>
                            </div>

                            <div class="ml-4 w-0 pt-4 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-700">Pending Leave Requests</dt>
                                    <dd class="text-3xl font-extrabold text-gray-900 tracking-tight">
                                        {{ $pendingLeaveRequests }}
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('employee-management.leave-requests') }}" 
                            class="inline-flex items-center text-sm font-semibold text-yellow-800 hover:text-yellow-900 transition duration-200">
                                View all 
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" 
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>


                <!-- Pending Shift Requests Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border-l-8 border-blue-500 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-15 h-15 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg fill="#000000" viewBox="-6 -6 65 65" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier">
                                        <path d="M46.8,19.6a1.6,1.6,0,0,1,1.6,1.6h0v24A4.81,4.81,0,0,1,43.6,50H8.4a4.81,4.81,0,0,1-4.8-4.8h0v-24a1.6,1.6,0,0,1,1.6-1.6H46.8ZM26,22.8a12,12,0,1,0,12,12,12,12,0,0,0-12-12Zm2.3,5.73,4.3,4.2a.62.62,0,0,1,0,.73l-4.3,4.15c-.31.31-.73,0-.73-.47V34.65a4.93,4.93,0,0,0-5.18,4.68,5.23,5.23,0,0,0,0,.56H19.28a8.23,8.23,0,0,1,8.11-8.35h.18V29C27.57,28.48,28,28.22,28.3,28.53ZM36.4,2a3.21,3.21,0,0,1,3.2,3.2h0V6.8h4a4.81,4.81,0,0,1,4.8,4.8h0v1.6a1.6,1.6,0,0,1-1.6,1.6H5.2a1.6,1.6,0,0,1-1.6-1.6h0V11.6A4.81,4.81,0,0,1,8.4,6.8h4V5.2a3.2,3.2,0,0,1,6.4,0h0V6.8H33.2V5.2A3.21,3.21,0,0,1,36.4,2Z"></path></g>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 w-0 pt-4 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-700">Pending Shift Requests</dt>
                                    <dd class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $pendingShiftRequests }}</dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('employee-management.shift-requests') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                View all →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Employee Profiles Not Set Up Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border-l-8 border-blue-500 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-15 h-15 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg viewBox="-5 -5 30 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>profile_image_close_round [#1328]</title> <desc>Created with Sketch.</desc> <defs> </defs> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Dribbble-Light-Preview" transform="translate(-300.000000, -2199.000000)" fill="#000000"> <g id="icons" transform="translate(56.000000, 160.000000)"> 
                                        <path d="M264.712423,2058.32983 C265.095859,2058.71234 265.095859,2059.33159 264.712423,2059.71312 C264.328987,2060.09563 263.708232,2060.09563 263.325776,2059.71312 L262.632453,2059.02148 L261.939129,2059.71312 C261.555693,2060.09563 260.934938,2060.09563 260.551502,2059.71312 C260.169046,2059.33159 260.169046,2058.71234 260.551502,2058.32983 L261.244825,2057.63819 L260.551502,2056.94654 C260.169046,2056.56404 260.169046,2055.94479 260.551502,2055.56326 C260.934938,2055.18075 261.555693,2055.18075 261.939129,2055.56326 L262.632453,2056.2549 L263.325776,2055.56326 C263.708232,2055.18075 264.328987,2055.18075 264.712423,2055.56326 C265.095859,2055.94479 265.095859,2056.56404 264.712423,2056.94654 L264.019099,2057.63819 L264.712423,2058.32983 Z M254.417502,2048.71725 C253.841858,2048.6703 253.673185,2048.67812 253.185799,2048.71432 C251.860934,2048.42964 250.864588,2047.25375 250.864588,2045.84796 C250.864588,2044.22988 252.183569,2042.91312 253.806554,2042.91312 C255.428558,2042.91312 256.74852,2044.22988 256.74852,2045.84796 C256.74852,2047.25668 255.747271,2048.43551 254.417502,2048.71725 L254.417502,2048.71725 Z M245.973079,2055.68848 C245.971117,2055.66793 245.961311,2055.6513 245.961311,2055.63076 L245.961311,2041.93484 C245.961311,2041.39483 246.399664,2040.95656 246.941966,2040.95656 L253.743792,2040.95656 C251.064642,2040.9908 248.903277,2043.16552 248.903277,2045.84502 C248.903277,2047.25864 249.513245,2048.52258 250.473306,2049.41575 C248.026571,2050.54957 246.261391,2052.89549 245.973079,2055.68848 L245.973079,2055.68848 Z M253.869316,2040.95656 L260.671142,2040.95656 C261.212464,2040.95656 261.651797,2041.39483 261.651797,2041.93484 L261.651797,2050.73936 C261.651797,2051.27937 262.09015,2051.71764 262.632453,2051.71764 C263.173774,2051.71764 263.613108,2051.27937 263.613108,2050.73936 L263.613108,2040.95656 C263.613108,2039.87654 262.734441,2039 261.651797,2039 L245.961311,2039 C244.877687,2039 244,2039.87654 244,2040.95656 L244,2056.60904 C244,2057.69004 244.877687,2058.5656 245.961311,2058.5656 L255.767865,2058.5656 C256.309187,2058.5656 256.74852,2058.12733 256.74852,2057.58732 C256.74852,2057.04731 256.309187,2056.60904 255.767865,2056.60904 L247.897125,2056.60904 C247.897125,2056.60904 247.891241,2056.54447 247.891241,2056.50534 C247.891241,2053.47659 250.202645,2050.98295 253.158341,2050.67479 C254.138996,2050.8049 255.829646,2050.39403 256.954458,2051.43687 C257.935113,2052.34569 259.416884,2050.89099 258.313646,2050.10935 C257.939036,2049.84423 257.538929,2049.60945 257.116266,2049.41085 C258.089076,2048.51768 258.709831,2047.25962 258.709831,2045.83524 C258.709831,2043.15573 256.547486,2040.9908 253.869316,2040.95656 L253.869316,2040.95656 Z" id="profile_image_close_round-[#1328]"> </path> </g> </g> </g> </g>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 w-0 pt-4 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-700">Profiles Not Set Up</dt>
                                    <dd class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $incompleteProfiles }}</dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('employee-management.employees', ['profile_status' => 'incomplete']) }}" class="text-sm font-medium text-red-600 hover:text-red-700">
                                Set up profiles →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="{{ route('employee-management.employees') }}" class="group relative bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg border border-gray-200 hover:border-gray-300">
                            <div>
                                <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-700 ring-4 ring-white">
                                    <svg fill="#000000" height="30px" width="30px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 330 330" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="XMLID_530_"> <g id="XMLID_531_"> 
                                        <path id="XMLID_532_" d="M115,147.75c20.389,0,38.531-9.78,50-24.889c11.469,15.109,29.611,24.889,50,24.889 c34.601,0,62.75-28.149,62.75-62.75S249.601,22.25,215,22.25c-20.389,0-38.531,9.78-50,24.889 C153.531,32.03,135.389,22.25,115,22.25c-34.601,0-62.75,28.149-62.75,62.75S80.399,147.75,115,147.75z M215,52.25 c18.059,0,32.75,14.691,32.75,32.75s-14.691,32.75-32.75,32.75S182.25,103.059,182.25,85S196.941,52.25,215,52.25z M115,52.25 c18.059,0,32.75,14.691,32.75,32.75s-14.691,32.75-32.75,32.75S82.25,103.059,82.25,85S96.941,52.25,115,52.25z"></path> </g> <g id="XMLID_536_"> <path id="XMLID_782_" d="M215,177.75c-17.373,0-34.498,3.942-50.022,11.44c-15.122-7.327-32.078-11.44-49.978-11.44 c-63.411,0-115,51.589-115,115c0,8.284,6.716,15,15,15h200h100c8.284,0,15-6.716,15-15C330,229.339,278.411,177.75,215,177.75z M31.325,277.75c7.106-39.739,41.923-70,83.675-70s76.569,30.261,83.675,70H31.325z M229.021,277.75 c-3.45-26.373-15.873-49.96-34.092-67.597c6.539-1.583,13.277-2.403,20.07-2.403c41.751,0,76.569,30.261,83.675,70H229.021z"></path> </g> </g> </g>
                                    </svg>
                                </span>
                            </div>
                            <div class="mt-8">
                                <h3 class="text-lg font-medium">
                                    <span class="absolute inset-0" aria-hidden="true"></span>
                                    Manage Employees
                                </h3>
                                <p class="mt-2 text-sm text-gray-500">
                                    View and setup employee profiles
                                </p>
                            </div>
                        </a>

                        <a href="{{ route('employee-management.alerts.create') }}" class="group relative bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg border border-gray-200 hover:border-gray-300">
                            <div>
                                <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-700 ring-4 ring-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                            </div>
                            <div class="mt-8">
                                <h3 class="text-lg font-medium">
                                    <span class="absolute inset-0" aria-hidden="true"></span>
                                    Create Alert
                                </h3>
                                <p class="mt-2 text-sm text-gray-500">
                                    Send system-wide notification
                                </p>
                            </div>
                        </a>

                        <a href="{{ route('employee-management.leave-requests') }}" class="group relative bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg border border-gray-200 hover:border-gray-300">
                            <div>
                                <span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-700 ring-4 ring-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </span>
                            </div>
                            <div class="mt-8">
                                <h3 class="text-lg font-medium">
                                    <span class="absolute inset-0" aria-hidden="true"></span>
                                    Leave Requests
                                </h3>
                                <p class="mt-2 text-sm text-gray-500">
                                    Review pending requests
                                </p>
                            </div>
                        </a>

                        <a href="{{ route('employee-management.alerts') }}" class="group relative bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg border border-gray-200 hover:border-gray-300">
                            <div>
                                <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-700 ring-4 ring-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-12a1 1 0 011-1h2a1 1 0 011 1v12z" />
                                    </svg>
                                </span>
                            </div>
                            <div class="mt-8">
                                <h3 class="text-lg font-medium">
                                    <span class="absolute inset-0" aria-hidden="true"></span>
                                    Manage Alerts
                                </h3>
                                <p class="mt-2 text-sm text-gray-500">
                                    View and edit alerts
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
