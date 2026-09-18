<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Session::has('success')): ?>
    <div class="mb-8"><?php if (isset($component)) { $__componentOriginal235d565c1d9da321d074440f16ad58d9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal235d565c1d9da321d074440f16ad58d9 = $attributes; } ?>
<?php $component = App\View\Components\Alerts\Success::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alerts.success'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Alerts\Success::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => ''.e(Session::get('success')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal235d565c1d9da321d074440f16ad58d9)): ?>
<?php $attributes = $__attributesOriginal235d565c1d9da321d074440f16ad58d9; ?>
<?php unset($__attributesOriginal235d565c1d9da321d074440f16ad58d9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal235d565c1d9da321d074440f16ad58d9)): ?>
<?php $component = $__componentOriginal235d565c1d9da321d074440f16ad58d9; ?>
<?php unset($__componentOriginal235d565c1d9da321d074440f16ad58d9); ?>
<?php endif; ?></div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Session::has('error')): ?>
    <div class="mb-8"><?php if (isset($component)) { $__componentOriginalb2cfbd8580a169635d009847c033d81f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb2cfbd8580a169635d009847c033d81f = $attributes; } ?>
<?php $component = App\View\Components\Alerts\Failed::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alerts.failed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Alerts\Failed::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => ''.e(Session::get('error')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb2cfbd8580a169635d009847c033d81f)): ?>
<?php $attributes = $__attributesOriginalb2cfbd8580a169635d009847c033d81f; ?>
<?php unset($__attributesOriginalb2cfbd8580a169635d009847c033d81f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb2cfbd8580a169635d009847c033d81f)): ?>
<?php $component = $__componentOriginalb2cfbd8580a169635d009847c033d81f; ?>
<?php unset($__componentOriginalb2cfbd8580a169635d009847c033d81f); ?>
<?php endif; ?></div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\katagigi-dignify\resources\views/components/flash-alerts.blade.php ENDPATH**/ ?>