@extends('dashboard')

@section('title', 'HR Timesheet Management')
@section('content')
  <div class="max-w-7xl mx-auto p-6 bg-gray-300">
    <header class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-2xl font-bold">Timesheet Management</h3>
        <p class="text-sm text-gray-600 mt-1">Manage employee timesheets, approvals, and payroll integration</p>
      </div>  
    </header>

    <main x-data="hrTimesheetApp()" x-init="init()" class="space-y-6">

      <!-- Quick Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Pending Approvals</p>
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
              <button @click="exportAllTimesheets()" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm">
                Export All
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
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button @click="bulkApproveAll()" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition-colors">
              <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <div class="font-medium">Bulk Approve</div>
              <div class="text-xs opacity-80">Approve all pending</div>
            </button>
            <button @click="generatePayrollReport()" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition-colors">
              <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
              <div class="font-medium">Payroll Report</div>
              <div class="text-xs opacity-80">Generate for current period</div>
            </button>
            <button @click="sendReminders()" class="bg-orange-500 hover:bg-orange-600 text-white p-4 rounded-lg text-center transition-colors">
              <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h9a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              <div class="font-medium">Send Reminders</div>
              <div class="text-xs opacity-80">To overdue employees</div>
            </button>
            <button @click="auditTimesheets()" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg text-center transition-colors">
              <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
              </svg>
              <div class="font-medium">Run Audit</div>
              <div class="text-xs opacity-80">Check for issues</div>
            </button>
          </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-gray-200 rounded-2xl p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
          <div class="space-y-3">
            <template x-for="activity in recentActivity" :key="activity.id">
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                  <div class="w-2 h-2 rounded-full" :class="getActivityColor(activity.type)"></div>
                  <div>
                    <div class="font-medium" x-text="activity.message"></div>
                    <div class="text-sm text-gray-500" x-text="activity.timestamp"></div>
                  </div>
                </div>
                <button @click="viewActivityDetails(activity.id)" class="text-blue-600 hover:text-blue-800 text-sm">View</button>
              </div>
            </template>
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
            <button @click="exportReport('excel')" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg">Export Excel</button>
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
        </div>
      </div>

      <!-- Timesheets Approvals Tab -->
      <div x-show="activeTab === 'approvals'" class="space-y-6">
        <!-- Bulk Actions -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Pending Approvals</h3>
            <div class="flex gap-2">
              <input type="checkbox" x-model="selectAllApprovals" @change="toggleSelectAll()" class="mr-2">
              <label class="text-sm text-gray-600 mr-4">Select All</label>
              <button @click="bulkApprove()" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">Bulk Approve</button>
              <button @click="bulkReject()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Bulk Reject</button>
            </div>
          </div>
          <div class="space-y-4">
            <template x-for="approval in pendingApprovals" :key="approval.id">
              <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                  <div class="flex items-center gap-3">
                    <input type="checkbox" x-model="approval.selected" class="mt-1">
                    <div class="flex-1">
                      <div class="flex items-center gap-2 mb-2">
                        <div class="font-medium text-lg" x-text="approval.employee"></div>
                        <span class="px-2 py-1 rounded-full text-xs" :class="getPriorityClass(approval.priority)" x-text="approval.priority"></span>
                      </div>
                      <div class="text-sm text-gray-600 mb-2">
                        <div x-text="approval.department + ' Department'"></div>
                        <div x-text="'Week: ' + approval.weekRange"></div>
                        <div x-text="'Submitted: ' + approval.submittedAt"></div>
                      </div>
                      <div class="flex items-center gap-4 text-sm">
                        <span class="text-green-600"><strong x-text="approval.totalHours"></strong> total hours</span>
                        <span class="text-orange-600" x-show="approval.overtimeHours > 0"><strong x-text="approval.overtimeHours"></strong> overtime</span>
                        <span class="text-blue-600"><strong x-text="approval.projectCount"></strong> projects</span>
                      </div>
                    </div>
                  </div>
                  <div class="flex gap-2 ml-4">
                    <button @click="viewTimesheetDetails(approval.id)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                      View Details
                    </button>
                    <button @click="approveTimesheet(approval.id)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                      Approve
                    </button>
                    <button @click="rejectTimesheet(approval.id)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                      Reject
                    </button>
                  </div>
                </div>
              </div>
            </template>
            <div x-show="pendingApprovals.length === 0" class="text-center text-gray-500 py-8">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-lg font-medium">All caught up!</p>
              <p class="text-sm">No pending timesheet approvals at the moment.</p>
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
                  <td colspan="8" class="py-8 text-center text-gray-500">
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
    </main>
  </div>

  <script>
  function hrTimesheetApp() {
    return {
      activeTab: 'overview',
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
      stats: {
        pendingApprovals: 12,
        totalEmployees: 45,
        overtimeHours: 128,
        weeklyHours: 1800
      },
      
      overviewStats: {
        submitted: 38,
        pending: 12,
        overdue: 5
      },
      
      // Recent Activity
      recentActivity: [
        { id: 1, type: 'approval', message: 'John Doe submitted timesheet for approval', timestamp: '2 minutes ago' },
        { id: 2, type: 'rejection', message: 'Jane Smith timesheet rejected - incomplete hours', timestamp: '15 minutes ago' },
        { id: 3, type: 'approval', message: 'Mike Johnson timesheet approved', timestamp: '1 hour ago' },
        { id: 4, type: 'submission', message: 'Sarah Wilson submitted weekly timesheet', timestamp: '2 hours ago' }
      ],
      
      // Reports
      reportFromDate: '',
      reportToDate: '',
      reportType: 'detailed',
      reportResults: [],
      
      // Approvals
      pendingApprovals: [
        {
          id: 1,
          employee: 'John Doe',
          department: 'Engineering',
          weekRange: 'Jan 8 - Jan 14, 2024',
          totalHours: 40,
          overtimeHours: 5,
          projectCount: 3,
          priority: 'High',
          submittedAt: '2 hours ago',
          selected: false
        },
        {
          id: 2,
          employee: 'Jane Smith',
          department: 'Marketing',
          weekRange: 'Jan 8 - Jan 14, 2024',
          totalHours: 37.5,
          overtimeHours: 0,
          projectCount: 2,
          priority: 'Medium',
          submittedAt: '1 day ago',
          selected: false
        }
      ],
      
      selectAllApprovals: false,
      
      // Employee Management (legacy)
      employeeFilter: 'all',
      employeeSearch: '',
      employees: [
        { id: 1, name: 'John Doe', initials: 'JD', position: 'Senior Developer', department: 'Engineering', weeklyHours: 40, status: 'Submitted', lastSubmission: '2 hours ago' },
        { id: 2, name: 'Jane Smith', initials: 'JS', position: 'Marketing Manager', department: 'Marketing', weeklyHours: 37.5, status: 'Pending', lastSubmission: '1 day ago' },
        { id: 3, name: 'Mike Johnson', initials: 'MJ', position: 'Sales Rep', department: 'Sales', weeklyHours: 35, status: 'Approved', lastSubmission: '3 days ago' },
        { id: 4, name: 'Sarah Wilson', initials: 'SW', position: 'HR Specialist', department: 'HR', weeklyHours: 40, status: 'Draft', lastSubmission: '5 days ago' }
      ],
      
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
      editingTimesheet: null,
      
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
        this.loadPendingApprovals();
        
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
        // Mock data - replace with actual API call
        this.monthlySummary = [
          { id: 1, project: 'Website Development', task: 'Frontend Development', totalHours: 160, status: 'approved' },
          { id: 2, project: 'Mobile App', task: 'API Integration', totalHours: 40, status: 'pending' },
          { id: 3, project: 'Database Migration', task: 'Data Migration', totalHours: 80, status: 'approved' }
        ];
        
        this.monthlyStats = {
          totalHours: 280,
          approvedHours: 240,
          pendingHours: 40,
          rejectedHours: 0
        };
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
        
        // Mock report results
        this.reportResults = [
          { id: 1, date: '2024-01-08', project: 'Website Development', task: 'Frontend', hours: 8, status: 'approved' },
          { id: 2, date: '2024-01-09', project: 'Mobile App', task: 'API Integration', hours: 6, status: 'pending' },
          { id: 3, date: '2024-01-10', project: 'Database Migration', task: 'Data Migration', hours: 7.5, status: 'approved' }
        ];
      },
      
      exportReport(format) {
        console.log(`Exporting report as ${format}...`);
        // Implement export functionality
      },
      
      approveTimesheet(id) {
        if (confirm('Approve this timesheet?')) {
          this.pendingApprovals = this.pendingApprovals.filter(a => a.id !== id);
          console.log('Timesheet approved:', id);
        }
      },
      
      rejectTimesheet(id) {
        const reason = prompt('Reason for rejection:');
        if (reason) {
          this.pendingApprovals = this.pendingApprovals.filter(a => a.id !== id);
          console.log('Timesheet rejected:', id, reason);
        }
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
        return this.employees.filter(emp => {
          const matchesFilter = this.employeeFilter === 'all' || emp.department.toLowerCase() === this.employeeFilter;
          const matchesSearch = this.employeeSearch === '' || 
                                emp.name.toLowerCase().includes(this.employeeSearch.toLowerCase()) ||
                                emp.position.toLowerCase().includes(this.employeeSearch.toLowerCase());
          return matchesFilter && matchesSearch;
        });
      },

      refreshData() {
        console.log('Refreshing HR data...');
        this.loadWeeklyTimesheet();
        this.loadMonthlyData();
        this.loadPendingApprovals();
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

      bulkApprove() {
        const selected = this.pendingApprovals.filter(a => a.selected);
        if (selected.length === 0) {
          alert('Please select timesheets to approve');
          return;
        }
        if (confirm(`Approve ${selected.length} selected timesheets?`)) {
          this.pendingApprovals = this.pendingApprovals.filter(a => !a.selected);
          this.stats.pendingApprovals -= selected.length;
        }
      },

      bulkReject() {
        const selected = this.pendingApprovals.filter(a => a.selected);
        if (selected.length === 0) {
          alert('Please select timesheets to reject');
          return;
        }
        const reason = prompt('Reason for bulk rejection:');
        if (reason) {
          this.pendingApprovals = this.pendingApprovals.filter(a => !a.selected);
          this.stats.pendingApprovals -= selected.length;
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
      async loadEmployeeTimesheets() {
        try {
          const params = new URLSearchParams({
            start_date: this.timesheetDateRange.start,
            end_date: this.timesheetDateRange.end,
            search: this.timesheetSearch,
            department: this.timesheetDepartmentFilter
          });
          
          const response = await fetch(`/timesheets/employee-timesheets?${params}`);
          const result = await response.json();
          
          if (result.success) {
            this.employeeTimesheets = result.data;
            this.availableDepartments = result.departments;
            this.loadTimesheetStats();
          } else {
            console.error('Failed to load employee timesheets:', result.message);
          }
        } catch (error) {
          console.error('Error loading employee timesheets:', error);
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
        // Open edit modal or inline editing
        this.editingTimesheet = { ...timesheet };
        console.log('Editing timesheet:', timesheet);
        // You can implement a modal here for editing
      },
      
      async deleteTimesheetEntry(timesheetId) {
        if (!confirm('Are you sure you want to delete this timesheet entry?')) {
          return;
        }
        
        try {
          const response = await fetch(`/timesheets/${timesheetId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Content-Type': 'application/json'
            }
          });
          
          const result = await response.json();
          
          if (result.success) {
            this.loadEmployeeTimesheets();
            alert('Timesheet entry deleted successfully!');
          } else {
            alert('Failed to delete timesheet entry: ' + result.message);
          }
        } catch (error) {
          console.error('Error deleting timesheet:', error);
          alert('Error deleting timesheet entry');
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
        
        // Set to Friday of current week
        endOfWeek.setDate(startOfWeek.getDate() + 4);
        
        this.timesheetDateRange.start = startOfWeek.toISOString().split('T')[0];
        this.timesheetDateRange.end = endOfWeek.toISOString().split('T')[0];
      }
    }
  }
  </script>
  
  <style>
  [x-cloak] { display: none !important; }
  </style>
@endsection
