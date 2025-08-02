<x-app-layout>
    {{-- Slot untuk memuat CSS khusus halaman ini --}}
    <x-slot name="styles">
        <style>
            /* Style kustom untuk tema tooltip 'light-border' */
            .tippy-box[data-theme~='light-border'] {
                background-color: white;
                color: #333;
                border: 1px solid #e5e7eb; /* gray-200 */
                border-radius: 0.75rem; /* rounded-xl */
                box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.05); /* shadow-lg */
            }

            .tippy-box[data-theme~='light-border'][data-placement^='top'] > .tippy-arrow::before {
                border-top-color: #e5e7eb;
            }
            .tippy-box[data-theme~='light-border'][data-placement^='bottom'] > .tippy-arrow::before {
                border-bottom-color: #e5e7eb;
            }

            .tippy-content {
                padding: 0;
            }

            /* FullCalendar Customizations */
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
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">Kegiatan: {{ $project->name }}</a> /
                    <span class="font-bold">{{ __('Kalender Kegiatan') }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Arahkan kursor ke event untuk melihat detail.</p>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Tombol Kembali: rounded-lg, shadow, hover scale --}}
                <i class="fas fa-arrow-left mr-2 text-gray-600"></i> {{-- Icon Font Awesome --}}
                Kembali ke Detail Kegiatan
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 flex-grow"> {{-- Latar belakang konsisten, flex-grow untuk mengisi ruang --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-full"> {{-- Ensure container takes full height --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 text-gray-900">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    {{-- Pustaka untuk Tooltip Interaktif --}}
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          locale: 'id',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
          },
          events: '{{ route('projects.tasks-json', $project) }}',
          
          eventDidMount: function(info) {
            // Menentukan ikon berdasarkan status atau prioritas
            let statusIcon = '';
            let statusColor = '';
            switch (info.event.extendedProps.status) {
                case 'pending':
                    statusIcon = '<i class="fas fa-hourglass-start text-yellow-500 mr-2"></i>';
                    statusColor = 'text-yellow-700';
                    break;
                case 'in_progress':
                    statusIcon = '<i class="fas fa-person-digging text-orange-500 mr-2"></i>';
                    statusColor = 'text-orange-700';
                    break;
                case 'for_review':
                    statusIcon = '<i class="fas fa-eye text-blue-500 mr-2"></i>';
                    statusColor = 'text-blue-700';
                    break;
                case 'completed':
                    statusIcon = '<i class="fas fa-check-circle text-green-500 mr-2"></i>';
                    statusColor = 'text-green-700';
                    break;
                default:
                    statusIcon = '<i class="fas fa-info-circle text-gray-500 mr-2"></i>';
                    statusColor = 'text-gray-700';
            }

            // Menentukan ikon dan warna prioritas
            let priorityIcon = '';
            let priorityColor = '';
            if (info.event.extendedProps.priority) {
                switch (info.event.extendedProps.priority.toLowerCase()) {
                    case 'high':
                        priorityIcon = '<i class="fas fa-fire text-red-500 mr-2"></i>';
                        priorityColor = 'text-red-700';
                        break;
                    case 'medium':
                        priorityIcon = '<i class="fas fa-grip-lines text-yellow-500 mr-2"></i>';
                        priorityColor = 'text-yellow-700';
                        break;
                    case 'low':
                        priorityIcon = '<i class="fas fa-leaf text-green-500 mr-2"></i>';
                        priorityColor = 'text-green-700';
                        break;
                }
            }


            tippy(info.el, {
              content: `
                <div class="p-4 text-left bg-white rounded-lg shadow-md border border-gray-100">
                    <h4 class="font-bold text-lg mb-2 text-gray-900 flex items-center border-b border-gray-200 pb-2">
                        <i class="fas fa-tasks text-indigo-600 mr-2"></i> ${info.event.title}
                    </h4>
                    <div class="space-y-1.5 text-gray-700 text-sm mt-3">
                        <p class="flex items-center"><strong class="font-semibold text-gray-600 w-28 flex-shrink-0">Proyek:</strong> <span class="font-medium text-gray-800">${info.event.extendedProps.project_name}</span></p>
                        <p class="flex items-center"><strong class="font-semibold text-gray-600 w-28 flex-shrink-0">Assignees:</strong> <span class="font-medium text-gray-800">${info.event.extendedProps.assignees}</span></p>
                        <p class="flex items-center"><strong class="font-semibold text-gray-600 w-28 flex-shrink-0">Deadline:</strong> <span class="font-medium text-gray-800">${moment(info.event.start).format('DD MMM YYYY')}</span></p>
                        <p class="flex items-center ${statusColor}"><strong class="font-semibold text-gray-600 w-28 flex-shrink-0">Status:</strong> ${statusIcon} <span class="font-medium">${info.event.extendedProps.status.replace(/_/g, ' ').toUpperCase()}</span></p>
                        <p class="flex items-center ${priorityColor}"><strong class="font-semibold text-gray-600 w-28 flex-shrink-0">Prioritas:</strong> ${priorityIcon} <span class="font-medium">${info.event.extendedProps.priority ? info.event.extendedProps.priority.toUpperCase() : 'N/A'}</span></p>
                    </div>
                </div>
              `,
              allowHTML: true,
              theme: 'light-border',
              placement: 'top',
              arrow: true,
              interactive: true,
              delay: [100, 0], // Sedikit delay sebelum muncul, tidak ada delay saat hilang
            });
          },

          eventClick: function(info) {
            info.jsEvent.preventDefault(); 
            if (info.event.url) {
              window.open(info.event.url, '_blank');
            }
          }
        });
        calendar.render();
      });
    </script>
    {{-- Include moment.js for date formatting in tooltips (FullCalendar needs it or you can use native Date methods) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    @endpush
</x-app-layout>