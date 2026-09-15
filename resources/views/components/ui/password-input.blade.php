@props(['model', 'id', 'placeholder' => '********', 'autocomplete' => 'current-password'])

<div x-data="{ show: false }" class="relative">
    <input
        :type="show ? 'text' : 'password'"
        id="{{ $id }}"
        wire:model="{{ $model }}"
        autocomplete="{{ $autocomplete }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-border bg-bg px-3 py-2 pr-10 text-sm text-text placeholder:text-text-muted focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600']) }}
    >

    <button
        type="button"
        x-on:click="show = !show"
        tabindex="-1"
        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-text-muted hover:text-text"
    >
        <x-heroicon-o-eye x-show="!show" class="h-4 w-4" style="display: none;" />
        <x-heroicon-o-eye-slash x-show="show" class="h-4 w-4" style="display: none;" />
    </button>
</div>
