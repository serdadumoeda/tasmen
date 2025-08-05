@props(['insights', 'previewInsights'])

@if($insights && $insights->isNotEmpty())
<div class="bg-white p-6 rounded-lg shadow-lg mb-6" x-data="{ showAll: false, visibleInsights: @json($insights->pluck('message')) }">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center">
            <i class="fas fa-lightbulb-on mr-3 text-yellow-500"></i>
            Rekomendasi & Peringatan
        </h3>
    </div>

    <div class="space-y-3">
        {{-- Initial Preview Insights --}}
        @foreach($previewInsights as $insight)
            <template x-if="visibleInsights.includes('{{ addslashes($insight['message']) }}')">
                <div class="p-4 rounded-lg flex items-start bg-{{ $insight['color'] }}-50 border-l-4 border-{{ $insight['color'] }}-400 relative">
                    <div class="flex-shrink-0 pt-1">
                        <i class="fas {{ $insight['icon'] }} text-{{ $insight['color'] }}-600 fa-lg"></i>
                    </div>
                    <div class="ml-4 flex-grow">
                        <p class="text-sm font-semibold text-{{ $insight['color'] }}-800">{{ $insight['severity'] }}</p>
                        <p class="text-sm text-{{ $insight['color'] }}-700">
                            {{ $insight['message'] }}
                            @if($insight['link'])
                                <a href="{{ $insight['link'] }}" class="font-bold underline hover:text-{{ $insight['color'] }}-900 ml-1">Lihat Detail</a>
                            @endif
                        </p>
                    </div>
                    <button @click="visibleInsights = visibleInsights.filter(i => i !== '{{ addslashes($insight['message']) }}')" class="absolute top-2 right-2 text-{{ $insight['color'] }}-400 hover:text-{{ $insight['color'] }}-600">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
            </template>
        @endforeach

        {{-- Collapsible Section for Remaining Insights --}}
        <div x-show="showAll" x-transition class="space-y-3">
            @foreach($insights->slice($previewInsights->count()) as $insight)
                <template x-if="visibleInsights.includes('{{ addslashes($insight['message']) }}')">
                    <div class="p-4 rounded-lg flex items-start bg-{{ $insight['color'] }}-50 border-l-4 border-{{ $insight['color'] }}-400 relative">
                        <div class="flex-shrink-0 pt-1">
                            <i class="fas {{ $insight['icon'] }} text-{{ $insight['color'] }}-600 fa-lg"></i>
                        </div>
                        <div class="ml-4 flex-grow">
                            <p class="text-sm font-semibold text-{{ $insight['color'] }}-800">{{ $insight['severity'] }}</p>
                            <p class="text-sm text-{{ $insight['color'] }}-700">
                                {{ $insight['message'] }}
                                @if($insight['link'])
                                    <a href="{{ $insight['link'] }}" class="font-bold underline hover:text-{{ $insight['color'] }}-900 ml-1">Lihat Detail</a>
                                @endif
                            </p>
                        </div>
                        <button @click="visibleInsights = visibleInsights.filter(i => i !== '{{ addslashes($insight['message']) }}')" class="absolute top-2 right-2 text-{{ $insight['color'] }}-400 hover:text-{{ $insight['color'] }}-600">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                </template>
            @endforeach
        </div>

        {{-- "Show More/Less" Button --}}
        @if($insights->count() > $previewInsights->count())
            <div class="text-center mt-4">
                <button @click="showAll = !showAll" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    <span x-show="!showAll">Lihat Semua ({{ $insights->count() }})</span>
                    <span x-show="showAll">Sembunyikan</span>
                </button>
            </div>
        @endif
    </div>
</div>
@endif
