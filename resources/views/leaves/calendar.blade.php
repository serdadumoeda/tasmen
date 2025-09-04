<x-app-layout>
    {{-- Slot untuk memuat CSS khusus halaman ini --}}
    <x-slot name="styles">
        <style>
            /* FullCalendar Customizations from projects/calendar.blade.php */
            .fc .fc-toolbar-title {
                font-family: 'Figtree', sans-serif;
                font-weight: 600; /* semibold */
                color: #374151; /* gray-700 */
                font-size: 1.5rem; /* text-2xl */
            }

            .fc .fc-button {
                background-color: #4f46e5; /* indigo-600 */
                border-color: #4f46e5;
                border-radius: 0.5rem; /* rounded-lg */
                font-weight: 600;
                box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); /* shadow-sm */
                transition: background-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }
            .fc .fc-button:hover {
                background-color: #4338ca; /* indigo-700 */
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); /* shadow-md */
            }
            .fc .fc-button:focus {
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.5), 0 0 0 6px rgba(99, 102, 241, 0.2); /* ring focus */
            }
            .fc .fc-button-primary {
                background-color: #6366f1; /* indigo-500 */
                border-color: #6366f1;
            }
            .fc .fc-button-primary:not(:disabled).fc-button-active {
                background-color: #4f46e5; /* indigo-600 */
                border-color: #4f46e5;
                box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06); /* shadow-inner */
            }
            .fc-daygrid-event {
                border-radius: 0.375rem; /* rounded-md */
                font-weight: 500;
                background-color: #ef4444; /* red-500 */
                border-color: #ef4444;
            }
            .fc-event-main {
                color: white;
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Kalender Cuti Tim') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Menampilkan semua cuti yang telah disetujui untuk tim Anda.</p>
            </div>
            <a href="{{ route('leaves.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-arrow-left mr-2 text-gray-600"></i>
                Kembali ke Dashboard Cuti
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 flex-grow">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 h-full">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          locale: 'id',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
          },
          events: {!! $events !!},
          displayEventTime: false, // Don't show time for all-day events
        });
        calendar.render();
      });
    </script>
    @endpush
</x-app-layout>
