<div x-data="notifications()" x-init="fetchUnread(); setInterval(() => fetchUnread(), 60000)" class="relative">
    <button @click="isOpen = !isOpen" class="relative z-10 block p-2 text-gray-700 bg-white border border-transparent rounded-md focus:border-blue-500 focus:ring-opacity-40 focus:ring-blue-300 focus:ring focus:outline-none">
        <i class="fas fa-bell"></i>
        <template x-if="count > 0">
            <span x-text="count" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
        </template>
    </button>

    <div x-show="isOpen" @click.away="isOpen = false" class="absolute right-0 z-20 w-80 mt-2 overflow-hidden bg-white rounded-md shadow-lg" x-cloak>
        <div class="py-2">
            <template x-for="notification in unread" :key="notification.id">
                <a :href="notification.data.url" @click="markAsRead(notification.id)" class="flex items-start px-4 py-3 -mx-2 border-b hover:bg-gray-100 bg-blue-50">
                    <div class="flex-shrink-0 w-10 text-center pt-1">
                        <template x-if="notification.type.includes('Leave')"><i class="fas fa-calendar-alt text-blue-500"></i></template>
                        <template x-if="notification.type.includes('Surat')"><i class="fas fa-envelope-open-text text-green-500"></i></template>
                        <template x-if="notification.type.includes('Task')"><i class="fas fa-tasks text-yellow-500"></i></template>
                        <template x-if="notification.type.includes('Peminjaman')"><i class="fas fa-people-arrows text-purple-500"></i></template>
                    </div>
                    <div class="mx-2">
                        <p class="text-sm text-gray-700" x-html="notification.data.message"></p>
                        <p class="text-xs text-gray-500" x-text="new Date(notification.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></p>
                    </div>
                </a>
            </template>
            <template x-if="unread.length === 0">
                <p class="text-center text-sm text-gray-500 py-4">Tidak ada notifikasi baru.</p>
            </template>
        </div>
        <a href="#" class="block bg-gray-800 text-white text-center font-bold py-2">Lihat Semua Notifikasi</a>
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