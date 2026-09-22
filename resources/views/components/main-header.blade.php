<header class="main-header" x-data>
    <div class="flex items-center gap-3">
        <slot name="title">
            <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ $title ?? config('app.name') }}</h1>
        </slot>
    </div>
    <div class="flex items-center gap-3">
        <slot name="actions" />
        <x-flash-alerts />
    </div>
</header>
