<div id="notificationModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full"
     data-recent-url="{{ route('notifications.recent') }}"
     data-mark-all-url="{{ route('notifications.mark-all-read') }}">
  <div class="relative w-full max-w-md max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 bg-white shadow text-white">
        <h3 class="text-xl font-medium">
          Notifications
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="notificationModal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      
      <!-- Modal body -->
      <div class="p-4 md:p-5 max-h-[70vh] overflow-y-auto">
        <div id="notification-modal-loading" class="py-6 text-center text-sm text-gray-500">
          Loading notifications...
        </div>
        <div id="notification-modal-empty" class="py-6 text-center text-sm text-gray-500" style="display:none;">
          No notifications found.
        </div>
        <div id="notification-modal-list" class="space-y-4"></div>
      </div>
      
      <!-- Modal footer -->
      <div class="flex items-center justify-between p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
        <button id="notification-mark-all" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
          Mark all as read
        </button>
        <button type="button" class="ms-3 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" data-modal-hide="notificationModal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const modalEl = document.getElementById('notificationModal');
    const listEl = document.getElementById('notification-modal-list');
    const emptyEl = document.getElementById('notification-modal-empty');
    const loadingEl = document.getElementById('notification-modal-loading');
    const badgeEl = document.getElementById('notification-badge-pill');
    const markAllBtn = document.getElementById('notification-mark-all');
    const modalTriggers = document.querySelectorAll('[data-modal-target="notificationModal"]');

    if (!listEl || !modalEl) return;
    const recentUrl = modalEl.dataset.recentUrl;
    const markAllUrl = modalEl.dataset.markAllUrl;

    const getIconSvg = (type) => {
      switch (type) {
        case 'success':
          return '<svg class="w-5 h-5 text-green-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
        case 'warning':
          return '<svg class="w-5 h-5 text-yellow-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
        case 'error':
          return '<svg class="w-5 h-5 text-red-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
        default:
          return '<svg class="w-5 h-5 text-blue-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>';
      }
    };

    const renderNotifications = (notifications) => {
      if (!notifications || notifications.length === 0) {
        listEl.innerHTML = '';
        emptyEl.style.display = 'block';
        return;
      }

      emptyEl.style.display = 'none';
      listEl.innerHTML = notifications.map((notification) => {
        const type = notification.type || 'info';
        const borderClass = type === 'success' ? 'border-green-500 bg-green-100' :
          type === 'warning' ? 'border-yellow-500 bg-yellow-100' :
          type === 'error' ? 'border-red-500 bg-red-100' : 'border-blue-500 bg-blue-100';
        const textClass = notification.read_at ? 'text-gray-500' : 'text-gray-900';
        const timeAgo = notification.time_ago || notification.created_at || '';

        return `
          <div class="p-4 border-l-4 ${borderClass} rounded-r-lg shadow">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                ${getIconSvg(type)}
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-semibold ${textClass}">${notification.title || 'Notification'}</h4>
                <div class="mt-1 text-sm ${notification.read_at ? 'text-gray-500' : 'text-gray-600'}">${notification.message || ''}</div>
                <div class="mt-2 text-xs text-gray-500">${timeAgo}</div>
              </div>
            </div>
          </div>
        `;
      }).join('');
    };

    const updateBadge = (count) => {
      if (!badgeEl) return;
      if (count > 0) {
        badgeEl.textContent = count;
        badgeEl.style.display = 'inline-flex';
      } else {
        badgeEl.style.display = 'none';
      }
    };

    const loadNotifications = () => {
      loadingEl.style.display = 'block';
      emptyEl.style.display = 'none';
      listEl.innerHTML = '';

      fetch(recentUrl, { headers: { 'Accept': 'application/json' } })
        .then((response) => response.json())
        .then((data) => {
          loadingEl.style.display = 'none';
          renderNotifications(data.notifications || []);
          updateBadge(data.unread_count || 0);
        })
        .catch(() => {
          loadingEl.style.display = 'none';
          emptyEl.style.display = 'block';
        });
    };

    modalTriggers.forEach((trigger) => {
      trigger.addEventListener('click', loadNotifications);
    });

    if (markAllBtn) {
      markAllBtn.addEventListener('click', () => {
        fetch(markAllUrl, {
          method: 'PATCH',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
          },
        })
          .then((response) => response.json())
          .then((data) => {
            updateBadge(data.unread_count || 0);
            loadNotifications();
          })
          .catch(() => {});
      });
    }
  })();
</script>
