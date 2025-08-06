<li class="text-sm flex justify-between items-center" id="attachment-{{ $attachment->id }}">
    <a href="{{ route('attachments.view', $attachment) }}" target="_blank" class="text-blue-600 hover:underline">{{ $attachment->filename }}</a>
    <form @submit.prevent="deleteAttachment($event, {{ $attachment->id }})">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs text-red-500 hover:text-red-700">&times;</button>
    </form>
</li>
