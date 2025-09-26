@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$width = match ($width) {
    '48' => 'w-48',
    '60' => 'w-60',
    default => $width,
};
@endphp

<div x-data="{
        open: false,
        position() {
            const trigger = this.$refs.trigger;
            const content = this.$refs.content;
            const rect = trigger.getBoundingClientRect();
            const margin = 4;

            let top = rect.bottom + margin;
            const contentHeight = content.offsetHeight;

            if (top + contentHeight > window.innerHeight) {
                top = rect.top - contentHeight - margin;
            }

            content.style.top = `${Math.max(margin, top)}px`;

            let left;
            if ('{{ $align }}' === 'right') {
                left = rect.right - content.offsetWidth;
            } else {
                left = rect.left;
            }

            const maxLeft = window.innerWidth - content.offsetWidth - margin;
            content.style.left = `${Math.min(Math.max(margin, left), Math.max(margin, maxLeft))}px`;
        }
    }" @click.outside="open = false" @close.stop="open = false" :class="{'submenu-is-open': open}">
    <div @click="open = ! open; if (open) { $nextTick(() => position()) }" x-ref="trigger">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-ref="content"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed z-50 {{ $width }} rounded-md shadow-lg"
            style="display: none;"
            @click="open = false">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
