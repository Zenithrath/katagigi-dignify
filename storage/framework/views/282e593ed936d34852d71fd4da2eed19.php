<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> <?php echo e(__('patient.master.index.title')); ?> <?php $__env->endSlot(); ?>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1><?php echo e(__('patient.master.index.title')); ?></h1>
                <p><?php echo e(__('patient.master.index.subtitle')); ?></p>
            </div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create patient')): ?>
                <a href="<?php echo e(route('patients.create')); ?>" class="clickable-primary py-2 px-4 rounded-xl">
                    <?php echo e(__('patient.master.index.buttons.add')); ?>

                </a>
            <?php endif; ?>
        </section>

        <?php if (isset($component)) { $__componentOriginal62f2d74fa8b498674280fbe32f0633f5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal62f2d74fa8b498674280fbe32f0633f5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash-alerts','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash-alerts'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal62f2d74fa8b498674280fbe32f0633f5)): ?>
<?php $attributes = $__attributesOriginal62f2d74fa8b498674280fbe32f0633f5; ?>
<?php unset($__attributesOriginal62f2d74fa8b498674280fbe32f0633f5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal62f2d74fa8b498674280fbe32f0633f5)): ?>
<?php $component = $__componentOriginal62f2d74fa8b498674280fbe32f0633f5; ?>
<?php unset($__componentOriginal62f2d74fa8b498674280fbe32f0633f5); ?>
<?php endif; ?>

        <div class="content-card">
            <form action="<?php echo e(route('patients.index')); ?>" method="GET" class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-600 mb-1.5 block"><?php echo e(__('form.labels.patient_keyword')); ?></label>
                    <input type="text" name="keyword" value="<?php echo e(request('keyword')); ?>" placeholder="<?php echo e(__('form.placeholders.keyword')); ?>" class="custom-input" />
                </div>
                <button class="clickable-primary py-2.5 px-5 rounded-xl text-sm font-bold" type="submit">
                    <?php echo e(__('patient.record.index.actions.find')); ?>

                </button>
            </form>
        </div>

        <div class="flex items-center gap-2 mt-1 mb-2">
            <span class="text-sm text-slate-500">Menampilkan <span class="font-bold text-slate-700"><?php echo e($patientList->total()); ?></span> data pasien</span>
        </div>

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column"><?php echo e(__('No.')); ?></th>
                        <th scope="col" class="index-column"><?php echo e(__('patient.master.index.table.name')); ?></th>
                        <th scope="col" class="column"><?php echo e(__('patient.master.index.table.mr')); ?></th>
                        <th scope="col" class="column"><?php echo e(__('patient.master.index.table.address')); ?></th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($patientList) > 0): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patientList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="column">
                                    <?php echo e(($patientList->currentPage()-1) * $patientList->perPage() + ++$index); ?>

                                </td>
<td>
                                    <div class="flex items-center gap-3.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patient->picture): ?>
                                            <img src="<?php echo e(asset('storage/' . $patient->picture)); ?>" alt="<?php echo e($patient->name); ?>" class="w-11 h-11 rounded-full object-cover border-2 border-slate-100 shrink-0" />
                                        <?php else: ?>
                                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center font-bold text-emerald-700 text-sm border-2 border-slate-100 shrink-0"><?php echo e(strtoupper(substr($patient->name, 0, 1))); ?></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="flex flex-col min-w-0">
                                            <a href="<?php echo e(route('patients.show', ['patient' => $patient->id])); ?>" class="font-bold text-slate-900 text-sm truncate hover:text-brand-600"><?php echo e($patient->name); ?></a>
                                            <span class="text-xs text-slate-400 truncate"><?php echo e($patient->email); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="column">
                                    <span><?php echo e($patient->code); ?></span>
                                </td>
                                <td class="column">
                                    <span><?php echo e(implode(', ', array_filter([$patient->village, $patient->district, $patient->regency], fn($value) => !is_null($value) && $value !== ''))); ?></span>
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        <a href="<?php echo e(route('patients.edit', ['patient' => $patient->id])); ?>" class="h-full">
                                            <?php echo e(__('patient.master.index.buttons.edit')); ?>

                                            <span class="sr-only"><?php echo e($patient->name); ?></span>
                                        </a>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete patient')): ?>
                                            <form action="<?php echo e(route('patients.destroy', ['patient' => $patient->id])); ?>"
                                                method="post">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('delete'); ?>
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                    type="submit"><?php echo e(__('patient.master.index.buttons.delete')); ?><span
                                                        class="sr-only"><?php echo e($patient->name); ?></span></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    <?php echo e(__('patient.master.index.table.empty')); ?>

                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </section>

        <?php if (isset($component)) { $__componentOriginal813ddc30a2c099adbe0f320e5b3df1f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813ddc30a2c099adbe0f320e5b3df1f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-paginator','data' => ['paginator' => $patientList]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-paginator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($patientList)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813ddc30a2c099adbe0f320e5b3df1f1)): ?>
<?php $attributes = $__attributesOriginal813ddc30a2c099adbe0f320e5b3df1f1; ?>
<?php unset($__attributesOriginal813ddc30a2c099adbe0f320e5b3df1f1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813ddc30a2c099adbe0f320e5b3df1f1)): ?>
<?php $component = $__componentOriginal813ddc30a2c099adbe0f320e5b3df1f1; ?>
<?php unset($__componentOriginal813ddc30a2c099adbe0f320e5b3df1f1); ?>
<?php endif; ?>
    </main>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\katagigi-dignify\resources\views/pages/patient/master/index.blade.php ENDPATH**/ ?>