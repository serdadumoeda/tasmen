@props(['status'])

@php
    $baseClasses = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full';
    $colorClasses = '';
    $displayText = \Illuminate\Support\Str::title(str_replace('_', ' ', $status));

    switch (strtolower($status)) {
        case 'approved':
        case 'completed':
        case 'selesai':
        case 'disetujui':
            $colorClasses = 'bg-green-100 text-green-800';
            break;

        case 'rejected':
        case 'cancelled':
        case 'dibatalkan':
            $colorClasses = 'bg-red-100 text-red-800';
            break;

        case 'pending':
        case 'menunggu persetujuan':
            $colorClasses = 'bg-yellow-100 text-yellow-800';
            break;

        case 'in_progress':
        case 'sedang berjalan':
        case 'approved_by_supervisor':
        case 'aktif':
            $colorClasses = 'bg-blue-100 text-blue-800';
            if ($status === 'approved_by_supervisor') {
                $displayText = 'Disetujui Atasan';
            }
            break;

        case 'on_hold':
        case 'ditunda':
        case 'draft':
            $colorClasses = 'bg-gray-200 text-gray-800';
            break;

        case 'not_started':
            $colorClasses = 'bg-gray-200 text-gray-800';
            $displayText = 'Belum Dimulai';
            break;

        default:
            $colorClasses = 'bg-gray-100 text-gray-700';
            break;
    }
@endphp

<span class="{{ $baseClasses }} {{ $colorClasses }}">
    {{ $displayText }}
</span>
