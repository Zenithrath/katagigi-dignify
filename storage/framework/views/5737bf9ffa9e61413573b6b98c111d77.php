<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e($title ?? config('app.name', 'KataGigi Dignify')); ?></title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet" />
        <link rel="shortcut icon" href="/favicon.svg" type="image/x-icon" />

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>
    <body class="antialiased">
        <div class="app-shell" x-data="{ sidebarOpen: false, sidebarCollapsed: false }" x-on:open-sidebar.window="sidebarOpen = true">

            
            <aside class="app-sidebar hidden lg:flex flex-col sticky top-0 h-screen bg-[#f8fafc] border-r border-slate-200/80"
                :class="sidebarCollapsed ? 'collapsed' : ''">
                <a href="<?php echo e(route('dashboard')); ?>" class="logo" wire:navigate>
                    <img src="<?php echo e(asset('assets/logo.svg')); ?>" alt="KataGigi" />
                </a>
                <div class="sidebar-scroll min-h-0 flex-1 overflow-y-auto">
                    <?php if (isset($component)) { $__componentOriginala84898f20479e38f2bc0cbb2808b7dee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala84898f20479e38f2bc0cbb2808b7dee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-nav','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala84898f20479e38f2bc0cbb2808b7dee)): ?>
<?php $attributes = $__attributesOriginala84898f20479e38f2bc0cbb2808b7dee; ?>
<?php unset($__attributesOriginala84898f20479e38f2bc0cbb2808b7dee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala84898f20479e38f2bc0cbb2808b7dee)): ?>
<?php $component = $__componentOriginala84898f20479e38f2bc0cbb2808b7dee; ?>
<?php unset($__componentOriginala84898f20479e38f2bc0cbb2808b7dee); ?>
<?php endif; ?>
                </div>
                
                <div class="p-3 border-t border-slate-200/80">
                    <button type="button" @click="sidebarCollapsed = !sidebarCollapsed"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors text-xs font-medium">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-panel-left-close'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4','x-show' => '!sidebarCollapsed']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-panel-left-open'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4','x-show' => 'sidebarCollapsed']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                        <span x-show="!sidebarCollapsed" x-transition>Collapse</span>
                    </button>
                </div>
            </aside>

            
            <div x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" style="display: none;">
                <div x-show="sidebarOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/50" x-on:click="sidebarOpen = false"></div>
                <aside class="app-sidebar absolute inset-y-0 left-0 flex h-full w-64 flex-col bg-[#f8fafc] border-r border-slate-200/80 shadow-xl z-50" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                    <div class="relative flex items-center justify-center px-4 pt-4 pb-4">
                        <a href="<?php echo e(route('dashboard')); ?>" class="logo !mb-0 !p-0" wire:navigate>
                            <img src="<?php echo e(asset('assets/logo.svg')); ?>" alt="KataGigi" />
                        </a>
                        <button type="button" x-on:click="sidebarOpen = false" class="absolute right-3 grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-200/60" aria-label="Tutup menu">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                        </button>
                    </div>
                    <div class="sidebar-scroll min-h-0 flex-1 overflow-y-auto" x-on:click="if ($event.target.closest('a')) sidebarOpen = false">
                        <?php if (isset($component)) { $__componentOriginala84898f20479e38f2bc0cbb2808b7dee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala84898f20479e38f2bc0cbb2808b7dee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-nav','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala84898f20479e38f2bc0cbb2808b7dee)): ?>
<?php $attributes = $__attributesOriginala84898f20479e38f2bc0cbb2808b7dee; ?>
<?php unset($__attributesOriginala84898f20479e38f2bc0cbb2808b7dee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala84898f20479e38f2bc0cbb2808b7dee)): ?>
<?php $component = $__componentOriginala84898f20479e38f2bc0cbb2808b7dee; ?>
<?php unset($__componentOriginala84898f20479e38f2bc0cbb2808b7dee); ?>
<?php endif; ?>
                    </div>
                </aside>
            </div>

            
            <div class="main-column flex min-w-0 flex-1 flex-col min-h-screen">
                
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.navigation', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2610296553-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

                
                <main class="content-area flex-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                        <div class="border-b border-slate-100 px-4 sm:px-6 py-4"><?php echo e($header); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="p-4 sm:p-6">
                        <?php echo e($slot); ?>

                    </div>
                </main>

                <?php if (isset($component)) { $__componentOriginalb3cc9df89cde89a13f9cf34ef8385cce = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3cc9df89cde89a13f9cf34ef8385cce = $attributes; } ?>
<?php $component = App\View\Components\MainFooter::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('main-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\MainFooter::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3cc9df89cde89a13f9cf34ef8385cce)): ?>
<?php $attributes = $__attributesOriginalb3cc9df89cde89a13f9cf34ef8385cce; ?>
<?php unset($__attributesOriginalb3cc9df89cde89a13f9cf34ef8385cce); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3cc9df89cde89a13f9cf34ef8385cce)): ?>
<?php $component = $__componentOriginalb3cc9df89cde89a13f9cf34ef8385cce; ?>
<?php unset($__componentOriginalb3cc9df89cde89a13f9cf34ef8385cce); ?>
<?php endif; ?>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($printable)): ?>
            <div class="print-base"><?php echo e($printable); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php echo $__env->yieldPushContent('scripts'); ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', () => {
                const elems = document.getElementsByClassName('selectable');
                for (let index = 0; index < elems.length; index++) {
                    try { initSelectable(elems[index]); } catch (_) {}
                }
            });
            function initSelectable(element) {
                if (!window.$ || !window.$.fn || typeof window.$.fn.select2 !== 'function') return;
                $(element).select2({
                    width: '100%',
                    id: element.getAttribute('id'),
                    dropdownParent: $(element).parent()
                });
            }
        </script>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH C:\laragon\www\katagigi-dignify\resources\views/layouts/app.blade.php ENDPATH**/ ?>