@props(['as' => 'div'])

<{{ $as }} {{ $attributes->merge(['class' => 'p-6 bg-white overflow-hidden rounded-xl border border-gray-200 shadow-md transition duration-300 ease-in-out hover:shadow-lg']) }}>
    {{ $slot }}
</{{ $as }}>
