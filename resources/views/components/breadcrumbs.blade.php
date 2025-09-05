@props(['breadcrumbs' => [], 'showBackButton' => true])

<div class="flex justify-between items-center mb-4">
    @if (count($breadcrumbs) > 0)
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse text-sm">
                @foreach ($breadcrumbs as $breadcrumb)
                    <li class="inline-flex items-center">
                        @if (!$loop->first)
                            <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                        @endif

                        {{-- If the breadcrumb has a URL and it's not the last one, it's a link --}}
                        @if (isset($breadcrumb['url']) && !$loop->last)
                            <a href="{{ $breadcrumb['url'] }}" class="font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-white">
                                {{ $breadcrumb['title'] }}
                            </a>
                        @else
                            {{-- The last breadcrumb is the current page, so it's not a link --}}
                            <span class="font-medium text-gray-500 dark:text-gray-400">
                                {{ $breadcrumb['title'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    @if($showBackButton)
    <button onclick="history.back()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
        Kembali
    </button>
    @endif
</div>
