@extends('dashboard-user')

@section('title', 'My Payroll')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Payroll</h1>
                    <p class="text-gray-600">View and download your payslips</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Current Month Summary -->
        @if($payslips->count() > 0)
        @php $currentPayslip = $payslips->first(); @endphp
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Current Period: {{ $currentPayslip['period'] }}</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${{ number_format($currentPayslip['gross_salary'], 2) }}</div>
                        <div class="text-sm text-gray-500">Gross Salary</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">${{ number_format($currentPayslip['deductions'], 2) }}</div>
                        <div class="text-sm text-gray-500">Total Deductions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">${{ number_format($currentPayslip['net_salary'], 2) }}</div>
                        <div class="text-sm text-gray-500">Net Salary</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Payslip History -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Payslip History</h3>
                <p class="text-sm text-gray-500 mt-1">Your payslip records</p>
            </div>
            
            @if($payslips->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Salary</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($payslips as $payslip)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $payslip['period'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($payslip['gross_salary'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($payslip['deductions'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${{ number_format($payslip['net_salary'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ is_object($payslip['generated_date']) ? $payslip['generated_date']->format('M j, Y') : date('M j, Y', strtotime($payslip['generated_date'])) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $payslip['status'] == 'available' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($payslip['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="action-btn action-btn--view mr-2">View Details</button>
                                @if($payslip['status'] == 'available')
                                <button class="action-btn action-btn--download">Download PDF</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No payslips available</h3>
                <p class="mt-1 text-sm text-gray-500">Your payslips will appear here once they are generated.</p>
            </div>
            @endif
        </div>

        <!-- Tax Information -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Tax Information</h3>
                <p class="text-sm text-gray-500 mt-1">Year-to-date tax summary</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-900">${{ number_format($payslips->sum('gross_salary'), 2) }}</div>
                        <div class="text-sm text-gray-500">YTD Gross</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-900">${{ number_format($payslips->sum('deductions'), 2) }}</div>
                        <div class="text-sm text-gray-500">YTD Deductions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-900">${{ number_format($payslips->sum('net_salary'), 2) }}</div>
                        <div class="text-sm text-gray-500">YTD Net</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $payslips->count() }}</div>
                        <div class="text-sm text-gray-500">Pay Periods</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
