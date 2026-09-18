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
     <?php $__env->slot('title', null, []); ?> <?php echo e($type == 'update' ? __('form.title.update.patient') : __('form.title.create.patient')); ?> <?php $__env->endSlot(); ?>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1><?php echo e($type == 'update' ? __('form.title.update.patient') : __('form.title.create.patient')); ?></h1>
                <p><?php echo e($type == 'update' ? 'Update patient information' : 'Register a new patient'); ?></p>
            </div>
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

        <form method="post" enctype="multipart/form-data" action="<?php echo e($action); ?>">
            <?php echo csrf_field(); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type == 'update'): ?>
                <?php echo method_field('put'); ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="content-card p-0 overflow-hidden">
                <?php if (isset($component)) { $__componentOriginal344544123fe2cfd06b79b908ecaea740 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal344544123fe2cfd06b79b908ecaea740 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.picture-upload','data' => ['data' => $data,'type' => ''.e($type).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('picture-upload'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($data),'type' => ''.e($type).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal344544123fe2cfd06b79b908ecaea740)): ?>
<?php $attributes = $__attributesOriginal344544123fe2cfd06b79b908ecaea740; ?>
<?php unset($__attributesOriginal344544123fe2cfd06b79b908ecaea740); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal344544123fe2cfd06b79b908ecaea740)): ?>
<?php $component = $__componentOriginal344544123fe2cfd06b79b908ecaea740; ?>
<?php unset($__componentOriginal344544123fe2cfd06b79b908ecaea740); ?>
<?php endif; ?>

                <div class="p-8 flex flex-col">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <div class="input-group">
                            <label for="name"><?php echo e(__('form.labels.name')); ?></label>
                            <input type="text" name="name" id="name" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.name')); ?>" value="<?php echo e($data->name ?? ''); ?>" required />
                            <small class="helper"><?php echo e(__('form.helpers.english_alpha_min', ['minlength' => 5])); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="email"><?php echo e(__('form.labels.email')); ?></label>
                            <input type="text" name="email" id="email" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.email')); ?>" value="<?php echo e($data->email ?? ''); ?>" />
                            <small class="helper"><?php echo e(__('form.helpers.valid_email')); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="payment_email"><?php echo e(__('form.labels.payment_email')); ?></label>
                            <div class="relative flex">
                                <input type="text" name="payment_email" id="payment_email" class="custom-input flex-1 !pr-20"
                                    placeholder="<?php echo e(__('form.placeholders.payment_email')); ?>"
                                    value="<?php echo e($data->payment_email ?? ''); ?>" />
                                <button class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-emerald-600 hover:text-emerald-700"
                                    @click.prevent="copyEmail()">
                                    <?php echo e(__('form.actions.use_email')); ?>

                                </button>
                            </div>
                            <small class="helper"><?php echo e(__('form.helpers.valid_email')); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payment_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="phone"><?php echo e(__('form.labels.phone')); ?></label>
                            <div class="relative">
                                <div class="absolute left-0 flex items-center px-4 h-11 text-sm font-medium text-slate-500 border-r border-slate-200 rounded-l-xl bg-slate-50">+62</div>
                                <input type="tel" name="phone" id="phone" class="custom-input !pl-14"
                                    placeholder="<?php echo e(__('form.placeholders.phone')); ?>"
                                    value="<?php echo e(preg_replace('/^62/', '', $data->phone) ?? ''); ?>" />
                            </div>
                            <small class="helper"><?php echo e(__('form.helpers.phone')); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="birthdate"><?php echo e(__('form.labels.birthdate')); ?></label>
                            <input type="date" name="birthdate" id="birthdate" class="custom-input"
                                value="<?php echo e($data->birthdate ?? ''); ?>" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['birthdate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="religion"><?php echo e(__('form.labels.religion')); ?></label>
                            <select id="religion" name="religion" autocomplete="religion" class="custom-select">
                                <option <?php echo e(!isset($data->religion) || !$data->religion ? 'selected' : ''); ?> disabled>
                                    <?php echo e(__('form.placeholders.religion')); ?>

                                </option>
                                <option value="ISLAM" <?php echo e($data->religion === 'ISLAM' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.islam')); ?>

                                </option>
                                <option value="CHRISTIANITY" <?php echo e($data->religion === 'CHRISTIANITY' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.christianity')); ?>

                                </option>
                                <option value="CATHOLIC" <?php echo e($data->religion === 'CATHOLIC' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.catholic')); ?>

                                </option>
                                <option value="BUDDHISM" <?php echo e($data->religion === 'BUDDHISM' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.buddhism')); ?>

                                </option>
                                <option value="HINDUISM" <?php echo e($data->religion === 'HINDUISM' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.hinduism')); ?>

                                </option>
                                <option value="KONGHUCHU" <?php echo e($data->religion === 'KONGHUCHU' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.konghuchu')); ?>

                                </option>
                                <option value="OTHER" <?php echo e($data->religion === 'OTHER' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.other_religion')); ?>

                                </option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['religion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="gender"><?php echo e(__('form.labels.gender')); ?></label>
                            <select id="gender" name="gender" autocomplete="gender-name" class="custom-select">
                                <option <?php echo e(!isset($data->gender) || !$data->gender || $data->gender === '' ? 'selected' : ''); ?> disabled>
                                    <?php echo e(__('form.placeholders.gender')); ?>

                                </option>
                                <option value="MALE" <?php echo e($data->gender === 'MALE' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.male')); ?>

                                </option>
                                <option value="FEMALE" <?php echo e($data->gender === 'FEMALE' ? 'selected' : ''); ?>>
                                    <?php echo e(__('form.labels.female')); ?>

                                </option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="village"><?php echo e(__('form.labels.village')); ?></label>
                            <input type="text" name="village" id="village" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.village')); ?>"
                                value="<?php echo e($data->village ?? ''); ?>" />
                            <small class="helper"><?php echo e(__('form.helpers.alphanumeric')); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['village'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-1">
                        <div class="input-group md:col-span-1">
                            <label for="street"><?php echo e(__('form.labels.street')); ?></label>
                            <input type="text" name="street" id="street" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.street')); ?>"
                                value="<?php echo e($data->street ?? ''); ?>" />
                            <small class="helper"><?php echo e(__('form.helpers.alpha_or_marks', ['marks' => '.,-/'])); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['street'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="zip_code"><?php echo e(__('form.labels.zipcode')); ?></label>
                            <input type="text" name="zip_code" id="zip_code" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.zipcode')); ?>"
                                value="<?php echo e($data->zip_code ?? ''); ?>" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['zip_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="tonarigumi"><?php echo e(__('form.labels.tonarigumi')); ?></label>
                            <input type="text" name="tonarigumi" id="tonarigumi" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.tonarigumi')); ?>"
                                value="<?php echo e($data->tonarigumi ?? ''); ?>" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tonarigumi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-1">
                        <div class="input-group">
                            <label for="district"><?php echo e(__('form.labels.district')); ?></label>
                            <input type="text" name="district" id="district" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.district')); ?>"
                                value="<?php echo e($data->district ?? ''); ?>" />
                            <small class="helper"><?php echo e(__('form.helpers.alphanumeric')); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['district'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="regency"><?php echo e(__('form.labels.city')); ?></label>
                            <input type="text" name="regency" id="regency" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.city')); ?>" value="<?php echo e($data->regency ?? ''); ?>" />
                            <small class="helper"><?php echo e(__('form.helpers.alphanumeric')); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['regency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="input-group">
                            <label for="province"><?php echo e(__('form.labels.state')); ?></label>
                            <input type="text" name="province" id="province" class="custom-input"
                                placeholder="<?php echo e(__('form.placeholders.state')); ?>"
                                value="<?php echo e($data->province ?? ''); ?>" />
                            <small class="helper"><?php echo e(__('form.helpers.alphanumeric')); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['province'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div x-data="{ showSosmed: false }" class="mt-4 pt-6 border-t border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer select-none mb-4">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" x-model="showSosmed">
                            <span class="text-sm font-medium text-slate-700"><?php echo e(__('patient.master.form.labels.needed_sosmed')); ?></span>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-1" x-show="showSosmed" x-transition>
                            <div class="input-group">
                                <label for="sosmed_fb"><?php echo e(__('form.labels.sosmed.facebook')); ?></label>
                                <input type="text" name="sosmed[]" id="sosmed_fb" class="custom-input"
                                    placeholder="<?php echo e(__('form.placeholders.sosmed')); ?>"
                                    value="<?php echo e($data->sosmed->facebook ?? ''); ?>" />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sosmed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label for="sosmed_ig"><?php echo e(__('form.labels.sosmed.instagram')); ?></label>
                                <input type="text" name="sosmed[]" id="sosmed_ig" class="custom-input"
                                    placeholder="<?php echo e(__('form.placeholders.sosmed')); ?>"
                                    value="<?php echo e($data->sosmed->instagram ?? ''); ?>" />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sosmed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label for="sosmed_tt"><?php echo e(__('form.labels.sosmed.tiktok')); ?></label>
                                <input type="text" name="sosmed[]" id="sosmed_tt" class="custom-input"
                                    placeholder="<?php echo e(__('form.placeholders.sosmed')); ?>"
                                    value="<?php echo e($data->sosmed->tiktok ?? ''); ?>" />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sosmed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label for="sosmed_tw"><?php echo e(__('form.labels.sosmed.twitter')); ?></label>
                                <input type="text" name="sosmed[]" id="sosmed_tw" class="custom-input"
                                    placeholder="<?php echo e(__('form.placeholders.sosmed')); ?>"
                                    value="<?php echo e($data->sosmed->twitter ?? ''); ?>" />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sosmed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="btn-submit !w-auto !px-8">
                            <?php echo e($type == 'update' ? __('form.actions.update') : __('form.actions.save')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

<?php if (! $__env->hasRenderedOnce('37cc4ce3-92d3-4d37-aa54-e4c68bd18e65')): $__env->markAsRenderedOnce('37cc4ce3-92d3-4d37-aa54-e4c68bd18e65');
$__env->startPush('scripts'); ?>
    <script type="text/javascript">
        function copyEmail() {
            let email = document.getElementById('email');
            let payment_email = document.getElementById('payment_email');
            payment_email.value = email.value;
        }
    </script>
<?php $__env->stopPush(); endif; ?>
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
<?php /**PATH C:\laragon\www\katagigi-dignify\resources\views/pages/patient/master/form.blade.php ENDPATH**/ ?>