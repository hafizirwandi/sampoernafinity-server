@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center justify-between gap-3">
        <button
            type="button"
            wire:click="previousPage('{{ $paginator->getPageName() }}')"
            wire:loading.attr="disabled"
            @disabled($paginator->onFirstPage())
            class="rounded-lg border border-border px-3 py-2 text-sm font-medium text-text hover:bg-surface-alt disabled:cursor-default disabled:opacity-40"
        >
            &laquo; Sebelumnya
        </button>

        <p class="hidden text-sm text-text-muted sm:block">
            Menampilkan <span class="font-medium text-text">{{ $paginator->firstItem() ?? 0 }}</span>
            &ndash; <span class="font-medium text-text">{{ $paginator->lastItem() ?? 0 }}</span>
            dari <span class="font-medium text-text">{{ $paginator->total() }}</span>
        </p>

        <p class="text-sm text-text-muted sm:hidden">
            Hal. {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
        </p>

        <button
            type="button"
            wire:click="nextPage('{{ $paginator->getPageName() }}')"
            wire:loading.attr="disabled"
            @disabled(! $paginator->hasMorePages())
            class="rounded-lg border border-border px-3 py-2 text-sm font-medium text-text hover:bg-surface-alt disabled:cursor-default disabled:opacity-40"
        >
            Selanjutnya &raquo;
        </button>
    </nav>
@endif
