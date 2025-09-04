<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kalender Tim') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            // Ganti 'events' dengan 'eventSources' untuk menggunakan API
            eventSources: [
                {
                    url: '/api/calendar-events',
                    method: 'GET',
                    // Tambahkan header otorisasi untuk Sanctum
                    headers: {
                        // NOTE: For production, generating a token on every page load is not ideal.
                        // A better approach would be to generate a long-lived token once
                        // and reuse it, or use a more robust authentication method for the API.
                        'Authorization': 'Bearer ' + '{{ auth()->user()->createToken("calendar-access")->plainTextToken }}'
                    },
                    failure: function() {
                        alert('Gagal memuat data kalender!');
                    },
                }
            ]
        });
        calendar.render();
    });
</script>
@endpush
</x-app-layout>
