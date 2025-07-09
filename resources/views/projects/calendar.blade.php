<x-app-layout>
    {{-- ========================================================== --}}
    {{-- =============      BAGIAN YANG DIPERBAIKI      ============ --}}
    {{-- ========================================================== --}}
    <x-slot name="styles">
        <style>
            /* Style kustom untuk tema tooltip 'light-border' */
            .tippy-box[data-theme~='light-border'] {
                background-color: white;
                color: #333;
                border: 1px solid #d1d5db; /* Warna border disesuaikan dengan skema abu-abu */
                border-radius: 0.5rem; /* Sudut membulat */
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            }

            .tippy-box[data-theme~='light-border'][data-placement^='top'] > .tippy-arrow::before {
                border-top-color: #d1d5db;
            }
            .tippy-box[data-theme~='light-border'][data-placement^='bottom'] > .tippy-arrow::before {
                border-bottom-color: #d1d5db;
            }

            .tippy-content {
                padding: 0;
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kalender Proyek: {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Arahkan kursor ke event untuk melihat detail.</p>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="mt-2 sm:mt-0 text-sm inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Kembali ke Detail
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
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
                tippy(info.el, {
                  content: `
                    <div class="p-3 text-left">
                        <h4 class="font-bold text-base mb-2 border-b border-gray-200 pb-1">${info.event.title}</h4>
                        <div class="space-y-1">
                            <p class="text-sm"><strong class="font-semibold text-gray-600">Proyek:</strong> ${info.event.extendedProps.project_name}</p>
                            <p class="text-sm"><strong class="font-semibold text-gray-600">Dikerjakan oleh:</strong> ${info.event.extendedProps.assignees}</p>
                            <p class="text-sm"><strong class="font-semibold text-gray-600">Status:</strong> ${info.event.extendedProps.status}</p>
                        </div>
                    </div>
                  `,
                  allowHTML: true,
                  theme: 'light-border',
                  placement: 'top',
                  arrow: true,
                  interactive: true,
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
    </x-slot>
</x-app-layout>