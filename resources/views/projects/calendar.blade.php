<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kalender Proyek: {{ $project->name }}
                </h2>
                 <p class="text-sm text-gray-500 mt-1">Lihat semua deadline tugas dalam satu tampilan.</p>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="mt-2 sm:mt-0 text-sm inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 transition">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Tampilan Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Container untuk Kalender --}}
                    <div id="calendar"></div>

                </div>
            </div>
        </div>
    </div>

    {{-- Library FullCalendar via CDN --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          locale: 'id', // Menambahkan lokalisasi Bahasa Indonesia
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
          },
          // INI PERBAIKANNYA: Cara pengambilan event yang lebih robust
          events: function(fetchInfo, successCallback, failureCallback) {
            fetch('{{ route('projects.tasks-json', $project) }}')
              .then(response => {
                if (!response.ok) {
                  throw new Error('Network response was not ok');
                }
                return response.json();
              })
              .then(data => {
                successCallback(data);
              })
              .catch(error => {
                console.error('Gagal mengambil data tugas untuk kalender:', error);
                failureCallback(error);
              });
          },
          loading: function(isLoading) {
              // Menambahkan indikator loading (opsional)
              if (isLoading) {
                  calendarEl.style.opacity = '0.5';
              } else {
                  calendarEl.style.opacity = '1';
              }
          },
          eventClick: function(info) {
            info.jsEvent.preventDefault(); 
            if (info.event.url) {
              window.open(info.event.url, '_blank'); // Buka link di tab baru
            }
          }
        });
        calendar.render();
      });
    </script>
</x-app-layout>