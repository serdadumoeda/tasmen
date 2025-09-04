<div x-data="notifications()" x-init="fetchUnread()" class="relative">
    <button @click="isOpen = !isOpen" class="relative z-10 block p-2 text-gray-700 bg-white border border-transparent rounded-md focus:border-blue-500 focus:ring-opacity-40 focus:ring-blue-300 focus:ring focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <template x-if="count > 0">
            <span x-text="count" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
        </template>
    </button>

    <div x-show="isOpen" @click.away="isOpen = false" class="absolute right-0 z-20 w-80 mt-2 overflow-hidden bg-white rounded-md shadow-lg" x-cloak>
        <div class="py-2">
            <template x-for="notification in unread" :key="notification.id">
                <a :href="notification.data.url" @click="markAsRead(notification.id)" class="flex items-center px-4 py-3 -mx-2 border-b hover:bg-gray-100">
                    <div class="mx-3">
                        <p class="text-sm text-gray-600" x-text="notification.data.message"></p>
                    </div>
                </a>
            </template>
            <template x-if="unread.length === 0">
                <p class="text-center text-sm text-gray-500 py-4">Tidak ada notifikasi baru.</p>
            </template>
        </div>
    </div>
</div>

<script>
    function notifications() {
        return {
            isOpen: false,
            unread: [],
            count: 0,
            fetchUnread() {
                fetch('{{ route("notifications.unread") }}')
                    .then(response => response.json())
                    .then(data => {
                        this.unread = data.unread;
                        this.count = data.count;
                    });
            },
            markAsRead(notificationId) {
                fetch('{{ route("notifications.markAsRead") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: notificationId })
                });
            }
        }
    }
</script>