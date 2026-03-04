@props([
    'title'   => '',
    'size'    => 'md',   // sm=360px, md=480px, lg=640px, xl=70vw
    'name'    => 'drawer',
])

{{--
    Usage:
    <x-drawer name="create-level" title="Tambah Job Level">
        <form ...>...</form>
    </x-drawer>

    Opener button:
    <button @click="$dispatch('open-drawer', 'create-level')">Buka</button>

    Close from inside form:
    <button type="button" @click="$dispatch('close-drawer', '{{ $name }}')">Batal</button>
--}}

@php
$widths = [
    'sm' => 'max-w-sm',      // 384px
    'md' => 'max-w-md',      // 448px (default)
    'lg' => 'max-w-lg',      // 512px
    'xl' => 'max-w-2xl',     // 672px
    'full' => 'max-w-4xl',   // 896px — employee create
];
$w = $widths[$size] ?? $widths['md'];
@endphp

<div
    x-data="{
        open: false,
        init() {
            window.addEventListener('open-drawer', (e) => {
                if (e.detail === '{{ $name }}') this.open = true;
            });
            window.addEventListener('close-drawer', (e) => {
                if (e.detail === '{{ $name }}') this.open = false;
            });
        }
    }"
    @keydown.escape.window="open = false"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm"
        style="display: none;"
    ></div>

    {{-- Drawer panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex flex-col w-full {{ $w }} bg-white shadow-2xl"
        style="display: none;"
        @click.stop
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
            <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
            <button
                type="button"
                @click="open = false"
                class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-md hover:bg-gray-100"
                aria-label="Tutup"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-5">
            {{ $slot }}
        </div>
    </div>
</div>
