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
     <?php $__env->slot('title', null, []); ?> <?php echo e($type == 'update' ? __('form.title.update.appointment') : __('form.title.create.appointment')); ?> <?php $__env->endSlot(); ?>

    <main class="main-table-container">
        <div class="flex gap-4 items-center">
            <a href="<?php echo e(route('appointments.index')); ?>" class="clickable-ghost w-9 h-9 rounded-xl">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-chevron-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-full h-full']); ?>
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
            </a>
            <h1 class="text-xl font-bold text-slate-900">
                <?php echo e($type == 'update' ? __('form.title.update.appointment') : __('form.title.create.appointment')); ?>

            </h1>
        </div>

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
            <div class="content-card">
                <?php echo csrf_field(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type == 'update'): ?>
                    <input type="hidden" name="id" value="<?php echo e($data->id); ?>" />
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type == 'update'): ?>
                    <?php echo method_field('put'); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="input-container" x-data="patientDataState">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type != 'update'): ?>
                        <div class="input-group">
                            <label for="patient_code"><?php echo e(__('form.labels.patient_keyword')); ?></label>
                            <div class="flex  flex-col md:flex-row gap-2 items-start">
                                <input type="text" name="patient_code" id="patient_code" class="w-full"
                                    placeholder="<?php echo e(__('form.placeholders.keyword')); ?>"
                                    value="<?php echo e($data->patient_code ?? (old('patient_code') ?? '')); ?>"
                                    x-model="patientKeyword" @keydown="handleKeyDown()" required />
                                <button class="clickable-primary px-4 py-2 rounded-md" @click.prevent="getPatientData()"
                                    id="check_patient"><?php echo e(__('form.actions.check')); ?></button>
                            </div>
                            <small class="helper"><?php echo e(__('form.helpers.alphanumeric')); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['patient_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="error"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <input type="hidden" name="patient_id" id="patient_id" x-model="patientID" />

                    <template x-if="isShown">
                        <table class="text-sm">
                            <thead>
                                <tr>
                                    <th class="py-2"><?php echo e(__('form.labels.patient_data')); ?></th>
                                    <th class="py-2"><?php echo e(__('form.labels.patient_phone')); ?></th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>

                            <tbody>
                                <template x-for="patient in patientList">
                                    <tr>
                                        <td class="flex flex-col pl-4 py-2">
                                            <span x-text="patient.patient_code"></span>
                                            <span x-text="patient.name"></span>
                                        </td>
                                        <td x-text="patient.phone" class="py-2"></td>
                                        <td>
                                            <template x-if="patientID != patient.id && patientID == ''">
                                                <button class="clickable-primary py-2 px-4 rounded-md"
                                                    @click.prevent="handleSelectPatient(patient.id)"><?php echo e(__('select')); ?></button>
                                            </template>
                                            <template x-if="patientID != '' && patientID == patient.id">
                                                <button class="clickable-primary py-2 px-4 rounded-md"
                                                    disabled><?php echo e(__('form.actions.selected')); ?></button>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </template>

                    <div class="input-group">
                        <label for="doctor_id"><?php echo e(__('form.labels.doctor')); ?></label>
                        <select name="doctor_id" id="doctor_id" class="selectable">
                            <option value="" disabled selected>
                                <?php echo e(__('form.placeholders.doctor')); ?>

                            </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($doctor->id); ?>"
                                    <?php echo e(isset($data->doctor_id) && $data->doctor_id == $doctor->id ? 'selected' : ''); ?>

                                    <?php if(old('doctor_id') == $doctor->id): ?> selected <?php endif; ?>>
                                    <?php echo e($doctor->nipp . ' - ' . $doctor->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['doctor_id'];
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
                        <label><?php echo e(__('form.labels.service')); ?></label>
                        <div id="service-container" class="flex flex-col gap-2 w-full" x-init="$watch('serviceIDList', unmountAddService)">
                            <template x-for="(serviceID, index) in serviceIDList">
                                <div class="flex gap-2">
                                    <select name="service_id[]" :id="`service-${index}`" class="selectable">
                                        <option value="" disabled selected
                                            x-text="`<?php echo e(__('form.placeholders.service_plan')); ?>`">
                                        </option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($service->id); ?>"
                                                :selected="serviceID == '<?php echo e($service->id); ?>'"
                                                x-text="`<?php echo e($service->code . ' - ' . $service->name); ?>`">
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                    <button class="clickable-ghost !border-danger-500 px-2 rounded-md stroke-danger-500"
                                        @click.prevent="handleRemoveService(index)">
                                        <div class="w-6 h-6">
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-trash-2'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6']); ?>
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
                                        </div>
                                    </button>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['service_id[]'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </template>
                        </div>
                    </div>

                    <div class="input-group items-start">
                        <button class="clickable-primary px-4 py-2 rounded-md"
                            @click.prevent="handleAddService()"><?php echo e(__('general.appointment.form.button.add_service')); ?></button>
                    </div>

                    <div class="input-group">
                        <label for="date" class="input-label"><?php echo e(__('form.labels.date')); ?></label>
                        <input type="date" name="date" id="date" class="input-text"
                            value="<?php echo e($data->date ?? (old('date') ?? now()->format('Y-m-d'))); ?>" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['date'];
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

                    <div class="flex flex-col md:flex-row gap-2">
                        <div class="flex-1">
                            <div class="input-group">
                                <label for="start_time" class="input-label"><?php echo e(__('form.labels.start_time')); ?></label>
                                <input type="time" name="start_time" id="start_time" class="input-text"
                                    value="<?php echo e(isset($data->time_start) && $data->time_start ? \Carbon\Carbon::parse($data->time_start)->format('H:i') : (old('start_time') ? date('H:i', strtotime(old('start_time'))) : '')); ?>" />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['start_time'];
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

                        <div class="flex-1">
                            <div class="input-group">
                                <label for="end_time" class="input-label"><?php echo e(__('form.labels.end_time')); ?></label>
                                <input type="time" name="end_time" id="end_time" class="input-text"
                                    value="<?php echo e(isset($data->time_end) && $data->time_end ? \Carbon\Carbon::parse($data->time_end)->format('H:i') : (old('end_time') ? date('H:i', strtotime(old('end_time'))) : '')); ?>" />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['end_time'];
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
                </div>

                <input type="submit"
                    value="<?php echo e($type == 'update' ? __('form.actions.update') : __('form.actions.save')); ?>" id="submit"
                    class="btn-submit" />
            </div>
        </form>
    </main>


<?php if (! $__env->hasRenderedOnce('88445975-f1a4-4061-8ad4-9fe2d6926d6d')): $__env->markAsRenderedOnce('88445975-f1a4-4061-8ad4-9fe2d6926d6d');
$__env->startPush('scripts'); ?>
    <script type="text/javascript">
        const patientDataState = {
            patientKeyword: "",
            patientID: "",
            patientList: [],
            serviceIDList: [""],
            isShown: false,
            init() {
                try {
                    this.patientID = "<?php echo e($data->patient_id ?? old('patient_id')); ?>";
                    let raw = <?php echo json_encode($data->services ?? old('service_id'), 15, 512) ?>;
                    if (Array.isArray(raw) && raw.length > 0) {
                        if (typeof raw[0] === 'object' && raw[0] !== null) {
                            this.serviceIDList = raw.map(s => s.id);
                        } else if (typeof raw[0] === 'string' || typeof raw[0] === 'number') {
                            this.serviceIDList = raw;
                        }
                    }
                    if (!this.patientID || this.serviceIDList.length === 0 || this.serviceIDList[0] === "") return;
                    this.patientKeyword = this.patientID;
                    this.getPatientData();
                } catch (e) {
                    console.warn('[appointment] init error:', e);
                }
            },
            handleSelectPatient(patientID) {
                this.patientID = patientID;
                this.patientList = this.patientList.filter((p) => p.id === this.patientID);
            },
            handleKeyDown() {
                this.isShown = false;
                this.patientList = [];
                this.patientID = "";
            },
            getPatientData() {
                const paramsString = new URLSearchParams({
                    search: this.patientKeyword
                }).toString();
                fetch("<?php echo e(route('api.appointments.get_patient')); ?>?" + paramsString, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        method: "GET"
                    })
                    .then((res) => res.json())
                    .then((data) => {
                        this.isShown = false;
                        this.patientList = [];
                        if (!data.data.length) return;

                        this.patientList = data.data;
                        this.isShown = true;
                    });
            },
            handleAddService() {
                this.serviceIDList.push("");
            },
            handleSelectService(index, event) {
                this.serviceIDList[index] = event.target.value;
            },
            handleRemoveService(index) {
                this.serviceIDList.splice(index, 1);
            },
            unmountAddService() {
                try {
                    const latest = document.querySelector('#service-container *:last-child select');
                    if (latest && window.$ && window.$.fn && typeof window.$.fn.select2 === 'function') {
                        initSelectable(latest);
                    }
                } catch (e) {}
            }
        };
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
<?php /**PATH C:\laragon\www\katagigi-dignify\resources\views/pages/general/appointment/form.blade.php ENDPATH**/ ?>