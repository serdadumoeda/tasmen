@props(['insights'])

@if($insights && $insights->isNotEmpty())
<div class="bg-white p-6 rounded-lg shadow-lg mb-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-lightbulb-on mr-3 text-yellow-500"></i>
        Rekomendasi & Peringatan
    </h3>
    <div class="space-y-3">
        @foreach($insights as $insight)
            <div class="p-4 rounded-lg flex items-start bg-{{ $insight['color'] }}-50 border-l-4 border-{{ $insight['color'] }}-400">
                <div class="flex-shrink-0 pt-1">
                    <i class="fas {{ $insight['icon'] }} text-{{ $insight['color'] }}-600 fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-semibold text-{{ $insight['color'] }}-800">{{ $insight['severity'] }}</p>
                    <p class="text-sm text-{{ $insight['color'] }}-700">
                        {{ $insight['message'] }}
                        @if($insight['link'])
                            <a href="{{ $insight['link'] }}" class="font-bold underline hover:text-{{ $insight['color'] }}-900 ml-1">Lihat Detail</a>
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
