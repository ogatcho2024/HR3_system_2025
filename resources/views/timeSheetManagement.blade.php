@extends('dashboard')

@section('title', 'HR Timesheet Management')
@section('content')
  <div class="max-w-7xl mx-auto p-6 bg-gray-300">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <main x-data="hrTimesheetApp()" x-init="init()" class="space-y-6">

      <!-- Quick Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Pending Timesheets</p>
              <p class="text-2xl font-bold text-orange-600" x-text="stats.pendingApprovals"></p>
            </div>
            <div class="bg-orange-100 p-3 rounded-full">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Employees</p>
              <p class="text-2xl font-bold text-blue-600" x-text="stats.totalEmployees"></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Overtime Hours</p>
              <p class="text-2xl font-bold text-red-600" x-text="stats.overtimeHours"></p>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Weekly Hours</p>
              <p class="text-2xl font-bold text-green-600" x-text="stats.weeklyHours"></p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Overview Tab -->
      <div x-show="activeTab === 'overview'" class="space-y-6">
        <!-- Current Week Summary -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">Current Week Overview</h2>
            <div class="flex items-center gap-2">
              <button @click="refreshData()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
              </button>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-blue-600">Submitted This Week</p>
                  <p class="text-2xl font-bold text-blue-700" x-text="overviewStats.submitted"></p>
                </div>
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="bg-yellow-50 to-yellow-100 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-yellow-600">Pending Review</p>
                  <p class="text-2xl font-bold text-yellow-700" x-text="overviewStats.pending"></p>
                </div>
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="bg-red-50 to-red-100 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-red-600">Overdue</p>
                  <p class="text-2xl font-bold text-red-700" x-text="overviewStats.overdue"></p>
                </div>
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly View -->
      <div x-show="activeTab === 'monthly'" class="space-y-6">
        <!-- Month Navigation -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="flex items-center justify-between mb-4">
            <button @click="changeMonth(-1)" class="p-2 hover:bg-gray-100 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <h2 class="text-xl font-semibold" x-text="currentMonthYear"></h2>
            <button @click="changeMonth(1)" class="p-2 hover:bg-gray-100 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="bg-blue-50 p-3 rounded-lg">
              <div class="text-2xl font-bold text-blue-600" x-text="monthlyStats.totalHours"></div>
              <div class="text-sm text-gray-600">Total Hours</div>
            </div>
            <div class="bg-green-50 p-3 rounded-lg">
              <div class="text-2xl font-bold text-green-600" x-text="monthlyStats.approvedHours"></div>
              <div class="text-sm text-gray-600">Approved Hours</div>
            </div>
            <div class="bg-yellow-50 p-3 rounded-lg">
              <div class="text-2xl font-bold text-yellow-600" x-text="monthlyStats.pendingHours"></div>
              <div class="text-sm text-gray-600">Pending Hours</div>
            </div>
            <div class="bg-red-50 p-3 rounded-lg">
              <div class="text-2xl font-bold text-red-600" x-text="monthlyStats.rejectedHours"></div>
              <div class="text-sm text-gray-600">Rejected Hours</div>
            </div>
          </div>
        </div>

        <!-- Monthly Summary Table -->
        <div class="bg-white rounded-2xl p-6 shadow overflow-x-auto">
          <h3 class="text-lg font-semibold mb-4">Monthly Summary by Project</h3>
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left py-3 px-4 font-medium text-gray-700">Project</th>
                <th class="text-left py-3 px-4 font-medium text-gray-700">Task</th>
                <th class="text-center py-3 px-4 font-medium text-gray-700">Hours</th>
                <th class="text-center py-3 px-4 font-medium text-gray-700">Status</th>
                <th class="text-center py-3 px-4 font-medium text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="summary in monthlySummary" :key="summary.id">
                <tr class="border-b hover:bg-gray-50">
                  <td class="py-3 px-4 font-medium" x-text="summary.project"></td>
                  <td class="py-3 px-4 text-gray-600" x-text="summary.task"></td>
                  <td class="py-3 px-4 text-center font-mono" x-text="summary.totalHours"></td>
                  <td class="py-3 px-4 text-center">
                    <span class="px-2 py-1 rounded-full text-xs" :class="getStatusClass(summary.status)" x-text="summary.status"></span>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <button @click="viewDetails(summary.id)" class="text-blue-600 hover:text-blue-800 text-sm">View Details</button>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Summary & Analytics Tab -->
      <div x-show="activeTab === 'summary&analytics'" class="space-y-6">
        <div class="bg-white rounded-2xl p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Generate Reports</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
              <input x-model="reportFromDate" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
              <input x-model="reportToDate" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
              <select x-model="reportType" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500">
                <option value="detailed">Detailed Report</option>
                <option value="summary">Summary Report</option>
                <option value="project">By Project</option>
                <option value="employee">By Employee</option>
              </select>
            </div>
          </div>
          <div class="flex gap-3">
            <button @click="generateReport()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">Generate Report</button>
            <button @click="exportReport('pdf')" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg">Export PDF</button>
          </div>
        </div>

        <!-- Report Results -->
        <div x-show="reportResults.length > 0" class="bg-white rounded-2xl p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Report Results</h3>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left py-3 px-4 font-medium text-gray-700">Date</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-700">Project</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-700">Task</th>
                  <th class="text-center py-3 px-4 font-medium text-gray-700">Hours</th>
                  <th class="text-center py-3 px-4 font-medium text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="result in reportResults" :key="result.id">
                  <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4" x-text="result.date"></td>
                    <td class="py-3 px-4" x-text="result.project"></td>
                    <td class="py-3 px-4" x-text="result.task"></td>
                    <td class="py-3 px-4 text-center" x-text="result.hours"></td>
                    <td class="py-3 px-4 text-center">
                      <span class="px-2 py-1 rounded-full text-xs" :class="getStatusClass(result.status)" x-text="result.status"></span>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="flex items-center justify-between mt-4" x-show="timesheetPagination.total > timesheetPagination.per_page">
            <div class="text-sm text-gray-600">
              Showing
              <span class="font-medium" x-text="((timesheetPagination.current_page - 1) * timesheetPagination.per_page) + 1"></span>
              -
              <span class="font-medium" x-text="Math.min(timesheetPagination.current_page * timesheetPagination.per_page, timesheetPagination.total)"></span>
              of
              <span class="font-medium" x-text="timesheetPagination.total"></span>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="prevTimesheetPage()"
                :disabled="timesheetPagination.current_page <= 1"
                class="px-3 py-1 border rounded text-sm"
                :class="timesheetPagination.current_page <= 1 ? 'text-gray-400 border-gray-200' : 'text-gray-700 border-gray-300 hover:bg-gray-50'">
                Prev
              </button>
              <span class="text-sm text-gray-600">
                Page <span class="font-medium" x-text="timesheetPagination.current_page"></span>
                of <span class="font-medium" x-text="timesheetPagination.last_page"></span>
              </span>
              <button
                @click="nextTimesheetPage()"
                :disabled="timesheetPagination.current_page >= timesheetPagination.last_page"
                class="px-3 py-1 border rounded text-sm"
                :class="timesheetPagination.current_page >= timesheetPagination.last_page ? 'text-gray-400 border-gray-200' : 'text-gray-700 border-gray-300 hover:bg-gray-50'">
                Next
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Timesheets Approvals Tab -->
      <div x-show="activeTab === 'approvals'" class="space-y-6">
        <!-- Pending Approvals Table -->
        <div class="bg-white rounded-2xl shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Pending Approvals</h3>
              <div class="flex items-center gap-3">
                <label class="flex items-center text-sm text-gray-600">
                  <input type="checkbox" x-model="selectAllApprovals" @change="toggleSelectAll()" class="mr-2 rounded">
                  Select All
                </label>
                <button @click="bulkApprove()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                  Bulk Approve
                </button>
                <button @click="bulkReject()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                  Bulk Reject
                </button>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr class="bg-green-950 text-white">
                  <th class="w-12 px-4 py-3 text-left">
                    <span class="sr-only">Select</span>
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Employee</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Date</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Project</th>
                  <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Hours</th>
                  <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Overtime</th>
                  <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Priority</th>
                  <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Submitted</th>
                  <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="approval in pendingApprovals" :key="approval.id">
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4">
                      <input type="checkbox" x-model="approval.selected" class="rounded">
                    </td>
                    <td class="px-4 py-4">
                      <div class="flex flex-col">
                        <div class="text-sm font-medium text-gray-900" x-text="approval.employee"></div>
                        <div class="text-sm text-gray-500" x-text="approval.department"></div>
                      </div>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-900" x-text="approval.work_date"></td>
                    <td class="px-4 py-4 text-sm text-gray-900" x-text="approval.project_name"></td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900" x-text="approval.totalHours"></td>
                    <td class="px-4 py-4 text-center text-sm text-orange-600 font-medium" x-text="approval.overtimeHours"></td>
                    <td class="px-4 py-4 text-center">
                      <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getPriorityClass(approval.priority)" x-text="approval.priority"></span>
                    </td>
                    <td class="px-4 py-4 text-center text-sm text-gray-500" x-text="approval.submittedAt"></td>
                    <td class="px-4 py-4 text-center">
                      <div class="flex items-center justify-center gap-2">
                        <button @click="approveTimesheet(approval.id)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium">
                          Approve
                        </button>
                        <button @click="rejectTimesheet(approval.id)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium">
                          Reject
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
            
            <!-- Empty state -->
            <div x-show="pendingApprovals.length === 0" class="text-center py-12">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-2">All caught up!</h3>
              <p class="text-sm text-gray-500">No pending timesheet approvals at the moment.</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Employees Timesheet Tab -->
      <div x-show="activeTab === 'employees'" class="space-y-6">
        <!-- Date Range and Filter Controls -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <h3 class="text-lg font-semibold">Employees Timesheet Management</h3>
          <div class="flex items-center justify-between mb-4">
            <div class="flex gap-2">
              <input x-model="timesheetDateRange.start" type="date" class="border border-gray-300 rounded-md px-3 py-2 text-sm" @change="loadEmployeeTimesheets()">
              <span class="flex items-center text-gray-500">to</span>
              <input x-model="timesheetDateRange.end" type="date" class="border border-gray-300 rounded-md px-3 py-2 text-sm" @change="loadEmployeeTimesheets()">
              <select x-model="timesheetDepartmentFilter" @change="loadEmployeeTimesheets()" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                <option value="">All Departments</option>
                <template x-for="dept in availableDepartments" :key="dept">
                  <option :value="dept" x-text="dept"></option>
                </template>
              </select>
              <input x-model="timesheetSearch" @input="loadEmployeeTimesheets()" type="text" placeholder="Search employees..." class="border border-gray-300 rounded-md px-3 py-2 text-sm w-64">
              <button @click="loadEmployeeTimesheets()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Summary Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-100 p-4 rounded-lg text-center">
              <div class="text-2xl font-bold text-blue-600" x-text="timesheetStats.total_timesheets || 0"></div>
              <div class="text-sm text-gray-600">Total Entries</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
              <div class="text-2xl font-bold text-green-600" x-text="timesheetStats.total_hours || 0"></div>
              <div class="text-sm text-gray-600">Total Hours</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg text-center">
              <div class="text-2xl font-bold text-orange-600" x-text="timesheetStats.total_overtime || 0"></div>
              <div class="text-sm text-gray-600">Overtime Hours</div>
            </div>
            <div class="bg-purple-100 p-4 rounded-lg text-center">
              <div class="text-2xl font-bold text-purple-600" x-text="timesheetStats.pending_approval || 0"></div>
              <div class="text-sm text-gray-600">Pending Approval</div>
            </div>
          </div>
        </div>
        
        <!-- Employee Timesheets Table -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left py-3 px-4 font-medium text-gray-700">Employee</th>
                  <th class="text-center py-3 px-4 font-medium text-gray-700">Date</th>
                  <th class="text-center py-3 px-4 font-medium text-gray-700">Time Start</th>
                  <th class="text-center py-3 px-4 font-medium text-gray-700">Time End</th>
                  <th class="text-center py-3 px-4 font-medium text-gray-700">Over Time</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-700">Total Hours</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-700">Night Diff</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-700">Status</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-700">Actions</th>
                  </tr>
                </thead>
                <tbody>
                <template x-for="timesheet in employeeTimesheets" :key="timesheet.id">
                  <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4">
                      <div>
                        <div class="font-medium" x-text="timesheet.employee"></div>
                        <div class="text-sm text-gray-500" x-text="timesheet.department"></div>
                      </div>
                    </td>
                    <td class="py-3 px-4 text-center font-mono" x-text="new Date(timesheet.date).toLocaleDateString()"></td>
                    <td class="py-3 px-4 text-center font-mono" x-text="timesheet.time_start"></td>
                    <td class="py-3 px-4 text-center font-mono" x-text="timesheet.time_end"></td>
                      <td class="py-3 px-4 text-center font-mono" x-text="timesheet.overtime_hours + 'h'"></td>
                      <td class="py-3 px-4 text-center font-mono font-semibold" x-text="timesheet.total_hours + 'h'"></td>
                      <td class="py-3 px-4 text-center font-mono" x-text="(timesheet.night_diff_minutes ?? 0) + 'm'"></td>
                      <td class="py-3 px-4 text-center">
                        <span class="px-2 py-1 rounded-full text-xs" :class="getTimesheetStatusClass(timesheet.status)" x-text="timesheet.status"></span>
                      </td>
                    <td class="py-3 px-4 text-center">
                      <div class="flex justify-center gap-1">
                        <button @click="editTimesheetEntry(timesheet)" class="text-blue-600 hover:text-blue-800 p-1" title="Edit Entry">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                          </svg>
                        </button>
                        <button @click="deleteTimesheetEntry(timesheet.id)" class="text-red-600 hover:text-red-800 p-1" title="Delete Entry">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
                <tr x-show="employeeTimesheets.length === 0">
                    <td colspan="9" class="py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                      <svg class="w-12 h-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                      </svg>
                      <p class="text-lg font-medium">No timesheet entries found</p>
                      <p class="text-sm">Try adjusting your date range or filters</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <!-- Compliance Tab -->
      <div x-show="activeTab === 'compliance'" class="space-y-6">
        <!-- Compliance Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-2xl p-6 shadow">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Compliance Rate</p>
                <p class="text-2xl font-bold text-green-600" x-text="complianceStats.rate + '%'"></p>
              </div>
              <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
              </svg>
            </div>
          </div>
          <div class="bg-white rounded-2xl p-6 shadow">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Policy Violations</p>
                <p class="text-2xl font-bold text-red-600" x-text="complianceStats.violations"></p>
              </div>
              <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
          </div>
          <div class="bg-white rounded-2xl p-6 shadow">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Audit Score</p>
                <p class="text-2xl font-bold text-blue-600" x-text="complianceStats.auditScore + '/100'"></p>
              </div>
              <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Compliance Issues -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Compliance Issues</h3>
          <div class="space-y-3">
            <template x-for="issue in complianceIssues" :key="issue.id">
              <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                  <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full mt-2" :class="getSeverityColor(issue.severity)"></div>
                    <div>
                      <div class="font-medium" x-text="issue.title"></div>
                      <div class="text-sm text-gray-600 mt-1" x-text="issue.description"></div>
                      <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                        <span x-text="'Employee: ' + issue.employee"></span>
                        <span x-text="'Date: ' + issue.date"></span>
                        <span class="px-2 py-1 rounded" :class="getSeverityClass(issue.severity)" x-text="issue.severity"></span>
                      </div>
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <button @click="resolveIssue(issue.id)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                      Resolve
                    </button>
                    <button @click="viewIssueDetails(issue.id)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                      Details
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
        
        <!-- Audit Trail -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Recent Audit Trail</h3>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left py-3 px-4 font-medium text-gray-700">Timestamp</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-700">User</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-700">Action</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-700">Details</th>
                  <th class="text-center py-3 px-4 font-medium text-gray-700">IP Address</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="audit in auditTrail" :key="audit.id">
                  <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 text-gray-600" x-text="audit.timestamp"></td>
                    <td class="py-3 px-4 font-medium" x-text="audit.user"></td>
                    <td class="py-3 px-4">
                      <span class="px-2 py-1 rounded-full text-xs" :class="getActionClass(audit.action)" x-text="audit.action"></span>
                    </td>
                    <td class="py-3 px-4 text-gray-600" x-text="audit.details"></td>
                    <td class="py-3 px-4 text-center font-mono text-xs" x-text="audit.ipAddress"></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- Edit Timesheet Modal -->
      <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="absolute inset-0 bg-opacity-2 backdrop-blur-sm" @click="showEditModal = false"></div>
          <div class="relative bg-white rounded-lg shadow-2xl max-w-md w-full border border-gray-200 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <h3 class="text-xl font-semibold text-gray-900">Edit Attendance Record</h3>
            <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 p-2 rounded-full transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="updateTimesheetEntry()">
            <div class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <input type="text" x-model="editingTimesheet.employee" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input type="date" x-model="editingTimesheet.date" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600">
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Time Start</label>
                  <input type="time" x-model="editingTimesheet.time_start" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Time End</label>
                  <input type="time" x-model="editingTimesheet.time_end" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea x-model="editingTimesheet.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="Optional notes..."></textarea>
              </div>
              
              <div x-show="editingTimesheet.total_hours" class="bg-gray-50 p-3 rounded-md">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total Hours:</span>
                  <span class="font-medium" x-text="editingTimesheet.total_hours + 'h'"></span>
                </div>
                <div class="flex justify-between text-sm mt-1">
                  <span class="text-gray-600">Overtime:</span>
                  <span class="font-medium text-orange-600" x-text="editingTimesheet.overtime_hours + 'h'"></span>
                </div>
              </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-lg">
              <button type="button" @click="showEditModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-xl hover:bg-gray-100 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="updating" class="px-4 ml-2 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 transition-colors font-medium">
                <span x-show="!updating">Update Record</span>
                <span x-show="updating">Updating...</span>
              </button>
            </div>
          </form>
          </div>
        </div>
      </div>

    </main>
  </div>

  <script>
  // API Base URL - uses Laravel's url() helper to work in any environment
  const TIMESHEET_API_BASE_URL = '{{ url("") }}';
  
  function hrTimesheetApp() {
    return {
      activeTab: 'employees',
      currentDate: new Date(),
      currentWeekStart: null,
      currentMonth: new Date().getMonth(),
      currentYear: new Date().getFullYear(),
      
      // Weekly data
      weeklyEntries: [],
      weekDays: [],
      weeklyTotalHours: 0,
      weeklyStatus: 'draft', // draft, submitted, approved, rejected
      
      // Monthly data
      monthlySummary: [],
      monthlyStats: {
        totalHours: 0,
        approvedHours: 0,
        pendingHours: 0,
        rejectedHours: 0
      },
      
      // HR Overview Stats
      stats: @json($stats),
      
      overviewStats: @json($overviewStats),
      
      // Reports
      reportFromDate: '',
      reportToDate: '',
      reportType: 'detailed',
      reportResults: [],
      
      // Approvals
      pendingApprovals: @json($pendingApprovals),
      
      selectAllApprovals: false,
      
      // Employee Management (legacy) - Now uses real data from employeeTimesheets
      employeeFilter: 'all',
      employeeSearch: '',
      employees: [], // This is now populated from real data in employeeTimesheets
      
      // New Employee Timesheets Data
      employeeTimesheets: [],
      timesheetStats: {
        total_employees: 0,
        total_timesheets: 0,
        total_hours: 0,
        total_overtime: 0,
        pending_approval: 0,
        approved: 0
      },
      timesheetDateRange: {
        start: new Date().toISOString().split('T')[0],
        end: new Date().toISOString().split('T')[0]
      },
      timesheetSearch: '',
      timesheetDepartmentFilter: '',
      availableDepartments: [],
      timesheetPagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      },
      editingTimesheet: {
        id: '',
        employee: '',
        date: '',
        time_start: '',
        time_end: '',
        status: '',
        notes: '',
        total_hours: '',
        overtime_hours: ''
      },
      showEditModal: false,
      updating: false,
      
      // Compliance Data
      complianceStats: {
        rate: 94,
        violations: 3,
        auditScore: 87
      },
      
      complianceIssues: [
        {
          id: 1,
          title: 'Excessive Overtime',
          description: 'Employee has logged more than 20 hours of overtime this week',
          employee: 'John Doe',
          date: '2024-01-12',
          severity: 'High'
        },
        {
          id: 2,
          title: 'Missing Timesheet',
          description: 'Employee has not submitted timesheet for the current week',
          employee: 'Mike Johnson',
          date: '2024-01-14',
          severity: 'Medium'
        }
      ],
      
      auditTrail: [
        {
          id: 1,
          timestamp: '2024-01-14 10:30:00',
          user: 'HR Admin',
          action: 'Approved',
          details: 'Approved timesheet for John Doe (Week 2)',
          ipAddress: '192.168.1.100'
        },
        {
          id: 2,
          timestamp: '2024-01-14 09:15:00',
          user: 'Jane Smith',
          action: 'Submitted',
          details: 'Submitted timesheet for approval (40 hours)',
          ipAddress: '192.168.1.105'
        }
      ],
      
      // Modal
      showAddEntryModal: false,
      newEntry: {
        project: '',
        task: '',
        description: ''
      },
      
      // Projects list
      projects: [
        { id: 1, name: 'Website Development' },
        { id: 2, name: 'Mobile App' },
        { id: 3, name: 'Database Migration' },
        { id: 4, name: 'System Maintenance' },
        { id: 5, name: 'Training & Development' }
      ],
      
      get currentWeekRange() {
        const start = new Date(this.currentWeekStart);
        const end = new Date(this.currentWeekStart);
        end.setDate(end.getDate() + 6);
        return `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;
      },
      
      get currentMonthYear() {
        return new Date(this.currentYear, this.currentMonth).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
      },

      getMonthParam() {
        const month = String(this.currentMonth + 1).padStart(2, '0');
        return `${this.currentYear}-${month}`;
      },
      
      get weeklyStatusClass() {
        const classes = {
          'draft': 'bg-gray-100 text-gray-800',
          'submitted': 'bg-yellow-100 text-yellow-800',
          'approved': 'bg-green-100 text-green-800',
          'rejected': 'bg-red-100 text-red-800'
        };
        return classes[this.weeklyStatus] || 'bg-gray-100 text-gray-800';
      },
      
      init() {
        // Check URL parameters for tab switching
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
          this.activeTab = tabParam;
        }
        
        this.setCurrentWeekStart();
        this.generateWeekDays();
        this.loadWeeklyTimesheet();
        this.loadMonthlyData();
        // this.loadPendingApprovals(); // Removed - data comes from backend via @json($pendingApprovals)
        
        // Set up employee timesheets date range and load data
        this.setCurrentWeekDateRange();
        this.loadEmployeeTimesheets();
      },
      
      setCurrentWeekStart() {
        const today = new Date();
        const dayOfWeek = today.getDay();
        const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
        this.currentWeekStart = new Date(today);
        this.currentWeekStart.setDate(today.getDate() + mondayOffset);
      },
      
      generateWeekDays() {
        this.weekDays = [];
        const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        for (let i = 0; i < 7; i++) {
          const date = new Date(this.currentWeekStart);
          date.setDate(date.getDate() + i);
          this.weekDays.push({
            name: days[i],
            date: date.toISOString().split('T')[0]
          });
        }
      },
      
      changeWeek(direction) {
        this.currentWeekStart.setDate(this.currentWeekStart.getDate() + (direction * 7));
        this.generateWeekDays();
        this.loadWeeklyTimesheet();
      },
      
      changeMonth(direction) {
        this.currentMonth += direction;
        if (this.currentMonth < 0) {
          this.currentMonth = 11;
          this.currentYear--;
        } else if (this.currentMonth > 11) {
          this.currentMonth = 0;
          this.currentYear++;
        }
        this.loadMonthlyData();
      },
      
      loadWeeklyTimesheet() {
        // Mock data - replace with actual API call
        this.weeklyEntries = [
          {
            id: 1,
            project: 'Website Development',
            task: 'Frontend Development',
            status: 'draft',
            hours: {
              '2024-01-08': 8,
              '2024-01-09': 7.5,
              '2024-01-10': 8,
              '2024-01-11': 6,
              '2024-01-12': 8
            }
          },
          {
            id: 2,
            project: 'Mobile App',
            task: 'API Integration',
            status: 'draft',
            hours: {
              '2024-01-08': 0,
              '2024-01-09': 0.5,
              '2024-01-10': 0,
              '2024-01-11': 2,
              '2024-01-12': 0
            }
          }
        ];
        this.calculateWeeklyTotals();
      },
      
      loadMonthlyData() {
        const month = this.getMonthParam();
        fetch(`${TIMESHEET_API_BASE_URL}/timesheets/monthly-summary?month=${month}`)
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              this.monthlySummary = result.summary || [];
              this.monthlyStats = result.stats || {
                totalHours: 0,
                approvedHours: 0,
                pendingHours: 0,
                rejectedHours: 0
              };
            } else {
              console.error('Failed to load monthly summary:', result.message);
            }
          })
          .catch(error => {
            console.error('Error loading monthly summary:', error);
          });
      },
      
      loadPendingApprovals() {
        // Mock data - replace with actual API call
        this.pendingApprovals = [
          {
            id: 1,
            employee: 'John Doe',
            weekRange: 'Jan 8 - Jan 14, 2024',
            totalHours: 40,
            submittedAt: '2 hours ago'
          },
          {
            id: 2,
            employee: 'Jane Smith',
            weekRange: 'Jan 8 - Jan 14, 2024',
            totalHours: 37.5,
            submittedAt: '1 day ago'
          }
        ];
      },
      
      getHoursForDay(entryId, date) {
        const entry = this.weeklyEntries.find(e => e.id === entryId);
        return entry ? (entry.hours[date] || 0) : 0;
      },
      
      updateHours(entryId, date, hours) {
        const entry = this.weeklyEntries.find(e => e.id === entryId);
        if (entry) {
          entry.hours[date] = parseFloat(hours) || 0;
          this.calculateWeeklyTotals();
          this.saveChanges();
        }
      },
      
      getEntryTotal(entryId) {
        const entry = this.weeklyEntries.find(e => e.id === entryId);
        if (!entry) return 0;
        return Object.values(entry.hours).reduce((sum, hours) => sum + (parseFloat(hours) || 0), 0);
      },
      
      calculateWeeklyTotals() {
        this.weeklyTotalHours = this.weeklyEntries.reduce((total, entry) => {
          return total + this.getEntryTotal(entry.id);
        }, 0);
      },
      
      addNewEntry() {
        const newId = Math.max(...this.weeklyEntries.map(e => e.id), 0) + 1;
        const newEntry = {
          id: newId,
          project: this.newEntry.project,
          task: this.newEntry.task,
          status: 'draft',
          hours: {}
        };
        
        // Initialize all days with 0 hours
        this.weekDays.forEach(day => {
          newEntry.hours[day.date] = 0;
        });
        
        this.weeklyEntries.push(newEntry);
        this.showAddEntryModal = false;
        this.newEntry = { project: '', task: '', description: '' };
        this.saveChanges();
      },
      
      editEntry(entryId) {
        // Implement edit functionality
        console.log('Edit entry:', entryId);
      },
      
      deleteEntry(entryId) {
        if (confirm('Are you sure you want to delete this entry?')) {
          this.weeklyEntries = this.weeklyEntries.filter(e => e.id !== entryId);
          this.calculateWeeklyTotals();
          this.saveChanges();
        }
      },
      
      submitWeeklyTimesheet() {
        if (confirm('Submit timesheet for approval?')) {
          this.weeklyStatus = 'submitted';
          // API call to submit timesheet
          console.log('Timesheet submitted');
        }
      },
      
      saveWeeklyDraft() {
        this.saveChanges();
        alert('Draft saved successfully!');
      },
      
      saveChanges() {
        // API call to save changes
        console.log('Saving changes...');
      },
      
      exportWeekly() {
        // Generate and download PDF
        console.log('Exporting weekly timesheet...');
      },
      
      generateReport() {
        if (!this.reportFromDate || !this.reportToDate) {
          alert('Please select both from and to dates');
          return;
        }

        const params = new URLSearchParams({
          from_date: this.reportFromDate,
          to_date: this.reportToDate,
          report_type: this.reportType
        });

        fetch(`${TIMESHEET_API_BASE_URL}/timesheets/report-results?${params}`)
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              this.reportResults = result.data || [];
            } else {
              alert(result.message || 'Failed to generate report');
            }
          })
          .catch(error => {
            console.error('Error generating report:', error);
            alert('An error occurred while generating the report.');
          });
      },
      
      exportReport(format) {
        if (!this.reportFromDate || !this.reportToDate) {
          alert('Please select both from and to dates');
          return;
        }

        if (format === 'pdf') {
          const params = new URLSearchParams({
            from_date: this.reportFromDate,
            to_date: this.reportToDate,
            report_type: this.reportType
          });
          const url = `${TIMESHEET_API_BASE_URL}/timesheets/export-pdf?${params}`;
          window.open(url, '_blank');
          return;
        }

        console.log(`Exporting report as ${format}...`);
      },
      
      async approveTimesheet(id) {
        if (!confirm('Approve this timesheet?')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/timesheets') }}/${id}/approve`;
        
        // Add CSRF token
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '_token';
        csrfField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfField);
        
        // Add method override for PATCH
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
      },
      
      async rejectTimesheet(id) {
        if (!confirm('Reject this timesheet?')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/timesheets') }}/${id}/reject`;
        
        // Add CSRF token
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '_token';
        csrfField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfField);
        
        // Add method override for PATCH
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        form.appendChild(methodField);
        
        // Add reason field
        const reasonField = document.createElement('input');
        reasonField.type = 'hidden';
        reasonField.name = 'reason';
        reasonField.value = 'Rejected by manager';
        form.appendChild(reasonField);
        
        document.body.appendChild(form);
        form.submit();
      },
      
      viewTimesheetDetails(id) {
        console.log('View timesheet details:', id);
        // Implement view details functionality
      },
      
      viewDetails(summaryId) {
        console.log('View summary details:', summaryId);
      },
      
      getStatusClass(status) {
        const classes = {
          'draft': 'bg-gray-100 text-gray-800',
          'pending': 'bg-yellow-100 text-yellow-800',
          'submitted': 'bg-blue-100 text-blue-800',
          'approved': 'bg-green-100 text-green-800',
          'rejected': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
      },

      // HR-specific functions
      get filteredEmployees() {
        // Create unique employee list from timesheets data
        const uniqueEmployees = [];
        const seenEmployeeIds = new Set();
        
        this.employeeTimesheets.forEach(timesheet => {
          if (!seenEmployeeIds.has(timesheet.employee_id)) {
            seenEmployeeIds.add(timesheet.employee_id);
            uniqueEmployees.push({
              id: timesheet.employee_id,
              name: timesheet.employee,
              department: timesheet.department,
              position: timesheet.position,
              status: timesheet.status,
              lastSubmission: timesheet.date
            });
          }
        });
        
        return uniqueEmployees.filter(emp => {
          const matchesFilter = this.employeeFilter === 'all' || emp.department.toLowerCase() === this.employeeFilter.toLowerCase();
          const matchesSearch = this.employeeSearch === '' || 
                                emp.name.toLowerCase().includes(this.employeeSearch.toLowerCase()) ||
                                (emp.position && emp.position.toLowerCase().includes(this.employeeSearch.toLowerCase()));
          return matchesFilter && matchesSearch;
        });
      },

      refreshData() {
        console.log('Refreshing HR data...');
        this.loadWeeklyTimesheet();
        this.loadMonthlyData();
        // this.loadPendingApprovals(); // Removed - data comes from backend
        // For real refresh, we should reload the page or make API call to get fresh data
        location.reload(); // Simple solution to get fresh backend data
      },

      exportAllTimesheets() {
        console.log('Exporting all employee timesheets...');
      },

      bulkApproveAll() {
        if (confirm('Approve all pending timesheets?')) {
          console.log('Bulk approving all timesheets');
          this.pendingApprovals = [];
          this.stats.pendingApprovals = 0;
        }
      },

      generatePayrollReport() {
        console.log('Generating payroll report...');
      },

      sendReminders() {
        console.log('Sending reminders to overdue employees...');
        alert('Reminders sent to 5 overdue employees');
      },

      auditTimesheets() {
        console.log('Running timesheet audit...');
      },

      getActivityColor(type) {
        const colors = {
          'approval': 'bg-green-500',
          'rejection': 'bg-red-500',
          'submission': 'bg-blue-500',
          'edit': 'bg-yellow-500'
        };
        return colors[type] || 'bg-gray-500';
      },

      viewActivityDetails(id) {
        console.log('View activity details:', id);
      },

      toggleSelectAll() {
        this.pendingApprovals.forEach(approval => {
          approval.selected = this.selectAllApprovals;
        });
      },

      async bulkApprove() {
        const selected = this.pendingApprovals.filter(a => a.selected);
        if (selected.length === 0) {
          alert('Please select timesheets to approve');
          return;
        }
        
        if (!confirm(`Approve ${selected.length} selected timesheets?`)) {
          return;
        }
        
        try {
          const response = await fetch('/timesheets/bulk-approve', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              timesheet_ids: selected.map(a => a.id)
            })
          });
          
          const result = await response.json();
          
          if (result.success) {
            // Remove approved timesheets from the list
            this.pendingApprovals = this.pendingApprovals.filter(a => !a.selected);
            // Update stats
            this.stats.pendingApprovals = Math.max(0, this.stats.pendingApprovals - result.approved_count);
            // Reset selection
            this.selectAllApprovals = false;
            alert(`Successfully approved ${result.approved_count} timesheets!`);
          } else {
            alert('Error: ' + result.message);
          }
        } catch (error) {
          console.error('Error bulk approving timesheets:', error);
          alert('An error occurred while approving the timesheets.');
        }
      },

      async bulkReject() {
        const selected = this.pendingApprovals.filter(a => a.selected);
        if (selected.length === 0) {
          alert('Please select timesheets to reject');
          return;
        }
        
        const reason = prompt('Reason for bulk rejection:');
        if (!reason) {
          return;
        }
        
        try {
          const response = await fetch('/timesheets/bulk-reject', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              timesheet_ids: selected.map(a => a.id),
              reason: reason
            })
          });
          
          const result = await response.json();
          
          if (result.success) {
            // Remove rejected timesheets from the list
            this.pendingApprovals = this.pendingApprovals.filter(a => !a.selected);
            // Update stats
            this.stats.pendingApprovals = Math.max(0, this.stats.pendingApprovals - result.rejected_count);
            // Reset selection
            this.selectAllApprovals = false;
            alert(`Successfully rejected ${result.rejected_count} timesheets!`);
          } else {
            alert('Error: ' + result.message);
          }
        } catch (error) {
          console.error('Error bulk rejecting timesheets:', error);
          alert('An error occurred while rejecting the timesheets.');
        }
      },

      getPriorityClass(priority) {
        const classes = {
          'High': 'bg-red-100 text-red-800',
          'Medium': 'bg-yellow-100 text-yellow-800',
          'Low': 'bg-green-100 text-green-800'
        };
        return classes[priority] || 'bg-gray-100 text-gray-800';
      },

      getEmployeeStatusClass(status) {
        const classes = {
          'Draft': 'bg-gray-100 text-gray-800',
          'Submitted': 'bg-blue-100 text-blue-800',
          'Pending': 'bg-yellow-100 text-yellow-800',
          'Approved': 'bg-green-100 text-green-800',
          'Rejected': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
      },

      viewEmployeeTimesheet(employeeId) {
        console.log('View employee timesheet:', employeeId);
      },

      editEmployeeTimesheet(employeeId) {
        console.log('Edit employee timesheet:', employeeId);
      },

      sendReminder(employeeId) {
        const employee = this.employees.find(emp => emp.id === employeeId);
        if (employee && confirm(`Send reminder to ${employee.name}?`)) {
          console.log('Sending reminder to:', employee.name);
          alert(`Reminder sent to ${employee.name}`);
        }
      },

      getSeverityColor(severity) {
        const colors = {
          'High': 'bg-red-500',
          'Medium': 'bg-yellow-500',
          'Low': 'bg-green-500'
        };
        return colors[severity] || 'bg-gray-500';
      },

      getSeverityClass(severity) {
        const classes = {
          'High': 'bg-red-100 text-red-800',
          'Medium': 'bg-yellow-100 text-yellow-800',
          'Low': 'bg-green-100 text-green-800'
        };
        return classes[severity] || 'bg-gray-100 text-gray-800';
      },

      resolveIssue(issueId) {
        if (confirm('Mark this compliance issue as resolved?')) {
          this.complianceIssues = this.complianceIssues.filter(issue => issue.id !== issueId);
          this.complianceStats.violations--;
        }
      },

      viewIssueDetails(issueId) {
        console.log('View issue details:', issueId);
      },

      getActionClass(action) {
        const classes = {
          'Approved': 'bg-green-100 text-green-800',
          'Rejected': 'bg-red-100 text-red-800',
          'Submitted': 'bg-blue-100 text-blue-800',
          'Modified': 'bg-yellow-100 text-yellow-800'
        };
        return classes[action] || 'bg-gray-100 text-gray-800';
      },
      
      // Employee Timesheet Methods
      async loadEmployeeTimesheets(page = 1) {
        try {
          const params = new URLSearchParams({
            start_date: this.timesheetDateRange.start,
            end_date: this.timesheetDateRange.end,
            search: this.timesheetSearch,
            department: this.timesheetDepartmentFilter,
            page: page,
            per_page: this.timesheetPagination.per_page
          });
          
          const response = await fetch(`${TIMESHEET_API_BASE_URL}/timesheets/employee-timesheets?${params}`);
          const result = await response.json();
          
          if (result.success) {
            this.employeeTimesheets = result.data;
            this.availableDepartments = result.departments;
            this.timesheetStats = result.stats; // Use stats from main response
            this.timesheetPagination = result.pagination || this.timesheetPagination;
            
            // Update legacy employees array for backward compatibility
            this.updateEmployeesFromTimesheets();
          } else {
            console.error('Failed to load employee timesheets:', result.message);
          }
        } catch (error) {
          console.error('Error loading employee timesheets:', error);
        }
      },

      nextTimesheetPage() {
        if (this.timesheetPagination.current_page < this.timesheetPagination.last_page) {
          this.loadEmployeeTimesheets(this.timesheetPagination.current_page + 1);
        }
      },

      prevTimesheetPage() {
        if (this.timesheetPagination.current_page > 1) {
          this.loadEmployeeTimesheets(this.timesheetPagination.current_page - 1);
        }
      },
      
      async loadTimesheetStats() {
        try {
          const params = new URLSearchParams({
            start_date: this.timesheetDateRange.start,
            end_date: this.timesheetDateRange.end
          });
          
          const response = await fetch(`/timesheets/stats?${params}`);
          const result = await response.json();
          
          if (result.success) {
            this.timesheetStats = result.stats;
          }
        } catch (error) {
          console.error('Error loading timesheet stats:', error);
        }
      },
      
      getTimesheetStatusClass(status) {
        const classes = {
          'draft': 'bg-gray-100 text-gray-800',
          'submitted': 'bg-blue-100 text-blue-800',
          'approved': 'bg-green-100 text-green-800',
          'rejected': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
      },
      
      editTimesheetEntry(timesheet) {
        console.log('Edit button clicked, timesheet:', timesheet);
        
        // Populate the edit form with current timesheet data
        this.editingTimesheet = {
          id: timesheet.id,
          employee: timesheet.employee,
          date: new Date(timesheet.date).toISOString().split('T')[0], // Format date as YYYY-MM-DD
          time_start: timesheet.time_start,
          time_end: timesheet.time_end,
          status: timesheet.status,
          notes: timesheet.notes || '',
          total_hours: timesheet.total_hours,
          overtime_hours: timesheet.overtime_hours
        };
        
        console.log('Setting showEditModal to true');
        this.showEditModal = true;
        console.log('showEditModal is now:', this.showEditModal);
      },
      
      async updateTimesheetEntry() {
        console.log('Starting update for timesheet:', this.editingTimesheet);
        this.updating = true;
        
        const updateData = {
          clock_in_time: this.editingTimesheet.time_start,
          clock_out_time: this.editingTimesheet.time_end,
          work_description: this.editingTimesheet.notes
        };
        
        console.log('Update data:', updateData);
          console.log('Request URL:', `${TIMESHEET_API_BASE_URL}/timesheets/${this.editingTimesheet.id}`);
        
        try {
          const response = await fetch(`${TIMESHEET_API_BASE_URL}/timesheets/${this.editingTimesheet.id}`, {
            method: 'PUT',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify(updateData)
          });
          
          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);
          
          const result = await response.json();
          console.log('Response data:', result);
          
          if (response.ok) {
            if (result.updated_record) {
              const idx = this.employeeTimesheets.findIndex(t => t.id === result.updated_record.id);
              if (idx !== -1) {
                this.employeeTimesheets[idx].time_start = result.updated_record.time_start || this.employeeTimesheets[idx].time_start;
                this.employeeTimesheets[idx].time_end = result.updated_record.time_end || this.employeeTimesheets[idx].time_end;
                this.employeeTimesheets[idx].total_hours = result.updated_record.total_hours || this.employeeTimesheets[idx].total_hours;
                this.employeeTimesheets[idx].overtime_hours = result.updated_record.overtime_hours || this.employeeTimesheets[idx].overtime_hours;
              }
            }
            this.showEditModal = false;
            this.loadEmployeeTimesheets(); // Refresh the data
            alert('Timesheet updated successfully!');
          } else {
            console.error('Server error response:', result);
            alert('Failed to update timesheet: ' + (result.message || result.error || 'Unknown error'));
          }
        } catch (error) {
          console.error('Network/Parse error:', error);
          alert('Error updating timesheet: ' + error.message);
        } finally {
          this.updating = false;
        }
      },
      
      async deleteTimesheetEntry(timesheetId) {
        if (!confirm('Are you sure you want to delete this timesheet?')) {
          return;
        }
        
        try {
          const response = await fetch(`${TIMESHEET_API_BASE_URL}/timesheets/${timesheetId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Content-Type': 'application/json'
            }
          });
          
          if (response.ok) {
            this.loadEmployeeTimesheets();
            alert('Timesheet deleted successfully!');
          } else {
            const result = await response.json();
            alert('Failed to delete timesheet: ' + (result.message || 'Unknown error'));
          }
        } catch (error) {
          console.error('Error deleting timesheet:', error);
          alert('Error deleting timesheet');
        }
      },
      
      setCurrentWeekDateRange() {
        const today = new Date();
        const startOfWeek = new Date(today);
        const endOfWeek = new Date(today);
        
        // Set to Monday of current week
        const dayOfWeek = today.getDay();
        const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
        startOfWeek.setDate(today.getDate() + mondayOffset);
        
        // Set to Sunday of current week (to include weekend)
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        
        this.timesheetDateRange.start = startOfWeek.toISOString().split('T')[0];
        this.timesheetDateRange.end = endOfWeek.toISOString().split('T')[0];
      },
      
      updateEmployeesFromTimesheets() {
        // Create unique employee list from timesheets data
        const uniqueEmployees = [];
        const seenEmployeeIds = new Set();
        
        this.employeeTimesheets.forEach(timesheet => {
          if (timesheet.employee_id && !seenEmployeeIds.has(timesheet.employee_id)) {
            seenEmployeeIds.add(timesheet.employee_id);
            
            // Calculate total hours for this employee
            const employeeTimesheets = this.employeeTimesheets.filter(t => t.employee_id === timesheet.employee_id);
            const totalHours = employeeTimesheets.reduce((sum, t) => sum + parseFloat(t.total_hours || 0), 0);
            
            // Find most recent submission
            const sortedTimesheets = employeeTimesheets.sort((a, b) => new Date(b.date) - new Date(a.date));
            const lastSubmission = sortedTimesheets.length > 0 ? 
              new Date(sortedTimesheets[0].date).toLocaleDateString() : 'No submissions';
            
            uniqueEmployees.push({
              id: timesheet.employee_id,
              name: timesheet.employee,
              initials: this.getInitials(timesheet.employee),
              position: timesheet.position || 'No Position',
              department: timesheet.department || 'No Department',
              weeklyHours: totalHours,
              status: this.capitalizeFirst(timesheet.status),
              lastSubmission: lastSubmission
            });
          }
        });
        
        this.employees = uniqueEmployees;
      },
      
      getInitials(fullName) {
        if (!fullName) return 'NA';
        return fullName.split(' ').map(name => name.charAt(0)).join('').toUpperCase().slice(0, 2);
      },
      
      capitalizeFirst(str) {
        if (!str) return 'Draft';
        return str.charAt(0).toUpperCase() + str.slice(1);
      }
    }
  }
  </script>
  
  <style>
  [x-cloak] { display: none !important; }
  </style>
@endsection
