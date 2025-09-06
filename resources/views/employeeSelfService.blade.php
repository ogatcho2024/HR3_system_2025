<script>
    // Redirect to the new employee self service dashboard
    window.location.href = '{{ route('employee.dashboard') }}';
</script>

<div class="p-6">
    <div class="text-center">
        <p class="text-gray-600">Redirecting to Employee Self Service Dashboard...</p>
        <p class="text-sm text-gray-500 mt-2">If you're not redirected automatically, <a href="{{ route('employee.dashboard') }}" class="text-blue-600 hover:text-blue-700">click here</a>.</p>
    </div>
</div>
