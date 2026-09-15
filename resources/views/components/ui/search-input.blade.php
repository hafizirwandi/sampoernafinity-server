@props(['model', 'placeholder' => 'Cari...'])

<div class="relative">
    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-muted" />

    <input
        type="search"
        wire:model.live.debounce.400ms="{{ $model }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-border bg-bg py-2 pl-9 pr-3 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600']) }}
    >
</div>
