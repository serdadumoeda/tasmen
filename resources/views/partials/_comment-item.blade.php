<div class="flex items-start space-x-2 text-sm">
    <span class="font-bold text-gray-800">{{ optional($comment->user)->name ?? 'User Dihapus' }}:</span>
    <p class="text-gray-700">{{ $comment->body }}</p>
</div>
