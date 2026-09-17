@props(['paginator'])

<section class="mt-4">
    <nav class="flex items-center justify-between border-t border-slate-200 px-1 py-3">
        <div class="flex w-0 flex-1">
            @if ($paginator->onFirstPage() === false)
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
                    <span class="hidden md:block">{{ __('Previous') }}</span>
                </a>
            @endif
        </div>

        <div class="hidden md:flex items-center gap-1">
            @if ($paginator->currentPage() > 3)
                <a href="{{ $paginator->url(1) }}"
                    class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">1</a>
            @endif

            @if ($paginator->currentPage() > 4)
                <span class="px-1 text-slate-400">...</span>
            @endif

            @foreach (range(1, $paginator->lastPage()) as $page)
                @if ($page >= $paginator->currentPage() - 2 && $page <= $paginator->currentPage() + 2)
                    <a href="{{ $paginator->url($page) }}"
                        class="{{ $page === $paginator->currentPage() ? 'px-3 py-1.5 text-sm font-medium rounded-lg bg-brand-600 text-white transition-colors' : 'px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors' }}"
                        aria-current="page">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->currentPage() + 2 < $paginator->lastPage() - 1)
                <span class="px-1 text-slate-400">...</span>
            @endif

            @if ($paginator->currentPage() + 2 < $paginator->lastPage())
                <a href="{{ $paginator->url($paginator->lastPage()) }}"
                    class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">{{ $paginator->lastPage() }}</a>
            @endif
        </div>

        <div class="flex w-0 flex-1 justify-end">
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors">
                    <span class="hidden md:block">{{ __('Next') }}</span>
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                </a>
            @endif
        </div>
    </nav>
</section>
