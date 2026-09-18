@props(['paginator'])

<section class="mt-4">
    <nav class="flex items-center justify-between bg-white rounded-2xl border border-slate-200 shadow-sm px-4 py-3">
        <div class="flex w-0 flex-1">
            @if ($paginator->onFirstPage() === false)
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-600 rounded-2xl border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
                    <span class="hidden md:block">{{ __('Previous') }}</span>
                </a>
            @endif
        </div>

        <div class="hidden md:flex items-center gap-1.5">
            @if ($paginator->currentPage() > 3)
                <a href="{{ $paginator->url(1) }}"
                    class="w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">1</a>
            @endif

            @if ($paginator->currentPage() > 4)
                <span class="px-1 text-slate-400">...</span>
            @endif

            @foreach (range(1, $paginator->lastPage()) as $page)
                @if ($page >= $paginator->currentPage() - 2 && $page <= $paginator->currentPage() + 2)
                    @if ($page === $paginator->currentPage())
                        <a href="{{ $paginator->url($page) }}"
                            class="min-w-[42px] h-10 flex items-center justify-center text-sm font-bold px-2 text-white cursor-pointer relative overflow-hidden"
                            style="background: radial-gradient(circle at 20% 20%, #165b38 0%, #0a331f 40%, #051d11 80%); box-shadow: inset 1.5px 2px 3px rgba(255,255,255,0.5), inset -2px -2.5px 4px rgba(0,0,0,0.8), 0 3px 8px rgba(5,29,17,0.45); border: 1px solid rgba(0,0,0,0.5); border-radius: 16px;"
                            aria-current="page">{{ $page }}</a>
                    @else
                        <a href="{{ $paginator->url($page) }}"
                            class="w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200"
                            aria-current="page">{{ $page }}</a>
                    @endif
                @endif
            @endforeach

            @if ($paginator->currentPage() + 2 < $paginator->lastPage() - 1)
                <span class="px-1 text-slate-400">...</span>
            @endif

            @if ($paginator->currentPage() + 2 < $paginator->lastPage())
                <a href="{{ $paginator->url($paginator->lastPage()) }}"
                    class="w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">{{ $paginator->lastPage() }}</a>
            @endif
        </div>

        <div class="flex w-0 flex-1 justify-end">
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-600 rounded-2xl border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
                    <span class="hidden md:block">{{ __('Next') }}</span>
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                </a>
            @endif
        </div>
    </nav>
</section>
