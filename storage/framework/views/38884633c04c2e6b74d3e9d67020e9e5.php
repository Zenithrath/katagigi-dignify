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
     <?php $__env->slot('title', null, []); ?> <?php echo e(__('patient.record.index._title')); ?> <?php $__env->endSlot(); ?>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1><?php echo e(__('patient.record.index._title')); ?></h1>
                <p><?php echo e(__('patient.record.index._subtitle')); ?></p>
            </div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create medical record')): ?>
                <a href="<?php echo e(route('medical-records.create')); ?>" class="clickable-primary py-2 px-4 rounded-xl">
                    <?php echo e(__('patient.record.index.actions.add')); ?>

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
            <form action="<?php echo e(route('medical-records.index')); ?>" method="GET" class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-600 mb-1.5 block"><?php echo e(__('patient.record.index.table.patient_keyword')); ?></label>
                    <input type="text" name="keyword" value="<?php echo e(request('keyword')); ?>" placeholder="<?php echo e(__('patient.record.index.placeholders.patient_id')); ?>" class="custom-input" />
                </div>
                <button class="clickable-primary py-2.5 px-5 rounded-xl text-sm font-bold" type="submit">
                    <?php echo e(__('patient.record.index.actions.find')); ?>

                </button>
            </form>
        </div>

        <div class="flex items-center gap-2 mt-1 mb-2">
            <span class="text-sm text-slate-500">Menampilkan <span class="font-bold text-slate-700"><?php echo e($medicalRecordList->total()); ?></span> data rekam medis</span>
        </div>

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column"><?php echo e(__('No.')); ?></th>
                        <th scope="col" class="index-column w-72"><?php echo e(__('patient.record.index.table.patient')); ?></th>
                        <th scope="col" class="column"><?php echo e(__('patient.record.index.table.phone')); ?></th>
                        <th scope="col" class="column"><?php echo e(__('patient.record.index.table.service')); ?></th>
                        <th scope="col" class="column w-56"><?php echo e(__('patient.record.index.table.doctor')); ?></th>
                        <th scope="col" class="column"><?php echo e(__('patient.record.index.table.date')); ?></th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($medicalRecordList) > 0): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $medicalRecordList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="column">
                                    <?php echo e(($medicalRecordList->currentPage() - 1) * $medicalRecordList->perPage() + ++$index); ?>

                                </td>
                                <td class="column w-72">
                                    <div class="flex flex-col">
                                        <a href="<?php echo e(route('medical-records.show', ['medical_record' => $record->id])); ?>"
                                            class="text-base mb-1"><?php echo e($record->patient_name); ?></a>
                                        <span><?php echo e($record->patient_code); ?></span>
                                        <span class="w-72 truncate"><?php echo e($record->patient_address); ?></span>
                                    </div>
                                </td>
                                <td class="column"><?php echo e($record->patient_phone); ?></td>
                                <td class="column">
                                    <ul>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = json_decode($record->services); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($item->code); ?> - <?php echo e($item->name); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </ul>
                                </td>
                                <td class="column w-56"><?php echo e($record->doctor_name); ?></td>
                                <td class="column"><?php echo e($record->appointment_date); ?></td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|doctor')): ?>
                                            <a href="<?php echo e(route('medical-records.edit', ['medical_record' => $record->id])); ?>"
                                                class="h-full">
                                                <?php echo e(__('patient.record.index.actions.edit')); ?>

                                                <span class="sr-only"><?php echo e($record->patient_name); ?></span>
                                            </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|doctor')): ?>
                                            <form
                                                action="<?php echo e(route('medical-records.destroy', ['medical_record' => $record->id])); ?>"
                                                method="post">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('delete'); ?>
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                    type="submit"><?php echo e(__('patient.record.index.actions.delete')); ?><span
                                                        class="sr-only"><?php echo e($record->patient_name); ?></span></button>
                                            </form>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    <?php echo e(__('patient.record.index.table.empty')); ?>

                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </section>

        <?php if (isset($component)) { $__componentOriginal813ddc30a2c099adbe0f320e5b3df1f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813ddc30a2c099adbe0f320e5b3df1f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-paginator','data' => ['paginator' => $medicalRecordList]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-paginator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($medicalRecordList)]); ?>
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
<?php /**PATH C:\laragon\www\katagigi-dignify\resources\views/pages/patient/record/index.blade.php ENDPATH**/ ?>