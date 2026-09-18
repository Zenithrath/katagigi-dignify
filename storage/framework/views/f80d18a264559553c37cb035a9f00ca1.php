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
     <?php $__env->slot('title', null, []); ?> <?php echo e(__('general.appointment.index._title')); ?> <?php $__env->endSlot(); ?>

    <main class="main-table-container" x-data="appointmentLookup">
        <section class="heading">
            <div>
                <h1><?php echo e(__('general.appointment.index._title')); ?></h1>
                <p><?php echo e(__('general.appointment.index._subtitle')); ?></p>
            </div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create appointment')): ?>
                <a href="<?php echo e(route('appointments.create')); ?>" class="clickable-primary py-2 px-4 rounded-lg">
                    <?php echo e(__('general.appointment.index.action.add')); ?>

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

        <section class="py-2 px-1 mt-2">
            <span class="text-sm text-slate-500" x-text="`Menampilkan ${pagination.total} janji`"></span>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|nurse')): ?>
            <section class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="column"><?php echo e(__('No.')); ?></th>
                            <th scope="col" class="index-column"><?php echo e(__('general.appointment.index.table.patient')); ?></th>
                            <th scope="col" class="column"><?php echo e(__('general.appointment.index.table.doctor')); ?></th>
                            <th scope="col" class="column"><?php echo e(__('general.appointment.index.table.service')); ?></th>
                            <th scope="col" class="column"><?php echo e(__('general.appointment.index.table.schedule')); ?></th>
                            <th scope="col" class="column"><?php echo e(__('general.appointment.index.table.status')); ?></th>
                            <th scope="col" class="action-column">
                                <span class="sr-only"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="isLoading">
                            <tr><td colspan="7" class="text-center py-12 text-slate-400">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Memuat data...
                                </div>
                            </td></tr>
                        </template>
                        <template x-if="!isLoading && appointmentList.length > 0">
                            <template x-for="(appointment, index) in appointmentList" :key="appointment.id">
                                <tr>
                                    <td class="column" x-text="(pagination.page - 1) * pagination.limit + index + 1"></td>
                                    <td class="index-column w-64 truncate">
                                        <div class="flex flex-col items-start justify-center gap-0">
                                            <a class="font-medium text-slate-900 hover:text-brand-600"
                                                :href="`<?php echo e(route('appointments.show', ['appointment' => '__ID__'])); ?>`.replace('__ID__', appointment.id)">
                                                <span x-text="appointment.patient_name"></span>
                                            </a>
                                            <small class="text-slate-400" x-text="appointment.patient_code"></small>
                                        </div>
                                    </td>
                                    <td class="column w-64">
                                        <div class="flex flex-col">
                                            <span class="text-slate-700" x-text="appointment.doctor_name"></span>
                                            <small class="text-slate-400">NIPP. <span x-text="appointment.doctor_nipp"></span></small>
                                        </div>
                                    </td>
                                    <td class="column">
                                        <span class="text-slate-700" x-text="count(appointment.services)+' Layanan'"></span>
                                    </td>
                                    <td class="column w-32">
                                        <div class="flex flex-col">
                                            <span class="text-slate-700" x-text="changeTimeFormat(appointment.time_start)+' - '+changeTimeFormat(appointment.time_end)"></span>
                                            <small class="text-slate-400" x-text="appointment.date"></small>
                                        </div>
                                    </td>
                                    <td class="column w-32">
                                        <template x-if="appointment.canceled_at && !appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <span class="badge badge-danger"><?php echo e(__('general.appointment.index.table.canceled')); ?></span>
                                        </template>
                                        <template x-if="appointment.confirmed_at && appointment.paid_at && appointment.recorded_at && !appointment.canceled_at">
                                            <span class="badge badge-info"><?php echo e(__('general.appointment.index.table.completed')); ?></span>
                                        </template>
                                        <template x-if="!appointment.canceled_at && appointment.confirmed_at && appointment.paid_at && !appointment.recorded_at">
                                            <span class="badge badge-success"><?php echo e(__('general.appointment.index.table.served and paid')); ?></span>
                                        </template>
                                        <template x-if="!appointment.canceled_at && appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <span class="badge badge-warning"><?php echo e(__('general.appointment.index.table.confirmed')); ?></span>
                                        </template>
                                        <template x-if="!appointment.canceled_at && !appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <span class="badge badge-neutral"><?php echo e(__('general.appointment.index.table.pending')); ?></span>
                                        </template>
                                    </td>
                                    <td class="action-column">
                                        <div class="flex items-center gap-2">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update appointment')): ?>
                                                <template x-if="!appointment.confirmed_at">
                                                    <a :href="`<?php echo e(route('appointments.confirm', ['appointment' => '__ID__'])); ?>`.replace('__ID__', appointment.id)"
                                                        class="text-sm text-brand-600 hover:text-brand-700">
                                                        <?php echo e(__('general.appointment.index.action.confirm')); ?>

                                                    </a>
                                                </template>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update appointment')): ?>
                                                <template x-if="!appointment.confirmed_at">
                                                    <a :href="`<?php echo e(route('appointments.edit', ['appointment' => '__ID__'])); ?>`.replace('__ID__', appointment.id)"
                                                        class="text-sm text-slate-500 hover:text-slate-700">
                                                        <?php echo e(__('general.appointment.index.action.edit')); ?>

                                                    </a>
                                                </template>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete appointment')): ?>
                                                <template x-if="!appointment.paid_at">
                                                    <form
                                                        :action="`<?php echo e(route('appointments.destroy', ['appointment' => '__ID__'])); ?>`.replace('__ID__', appointment.id)"
                                                        method="post"
                                                        @submit.prevent="if(confirm('Batalkan janji ini?')) $el.submit()">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('delete'); ?>
                                                        <button class="text-sm text-slate-400 hover:text-red-600 transition-colors" type="submit">
                                                            <?php echo e(__('general.appointment.index.action.delete')); ?>

                                                        </button>
                                                    </form>
                                                </template>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                        <template x-if="!isLoading && appointmentList.length === 0">
                            <tr><td colspan="7" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    <span class="text-sm"><?php echo e(__('general.appointment.index.table.empty')); ?></span>
                                </div>
                            </td></tr>
                        </template>
                    </tbody>
                </table>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'doctor')): ?>
            <section class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="index-column"><?php echo e(__('general.appointment.index.table.patient')); ?></th>
                            <th scope="col" class="column"><?php echo e(__('general.appointment.index.table.doctor')); ?></th>
                            <th scope="col" class="column"><?php echo e(__('general.appointment.index.table.service')); ?></th>
                            <th scope="col" class="column"><?php echo e(__('general.appointment.index.table.schedule')); ?></th>
                            <th scope="col" class="column"><?php echo e(__('general.appointment.index.table.status')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="isLoading">
                            <tr><td colspan="5" class="text-center py-12 text-slate-400">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Memuat data...
                                </div>
                            </td></tr>
                        </template>
                        <template x-if="!isLoading && appointmentList.length > 0">
                            <template x-for="appointment in appointmentList" :key="appointment.id">
                                <tr>
                                    <td class="index-column w-64 truncate">
                                        <div class="flex flex-col items-start justify-center gap-0">
                                            <a class="font-medium text-slate-900 hover:text-brand-600"
                                                :href="`<?php echo e(route('appointments.show', ['appointment' => '__ID__'])); ?>`.replace('__ID__', appointment.id)">
                                                <span x-text="appointment.patient_name"></span>
                                            </a>
                                            <small class="text-slate-400" x-text="appointment.patient_code"></small>
                                        </div>
                                    </td>
                                    <td class="column w-64">
                                        <div class="flex flex-col">
                                            <span class="text-slate-700" x-text="appointment.doctor_name"></span>
                                            <small class="text-slate-400">NIPP. <span x-text="appointment.doctor_nipp"></span></small>
                                        </div>
                                    </td>
                                    <td class="column">
                                        <span class="text-slate-700" x-text="count(appointment.services)+' Layanan'"></span>
                                    </td>
                                    <td class="column w-32">
                                        <div class="flex flex-col">
                                            <span class="text-slate-700" x-text="changeTimeFormat(appointment.time_start)+' - '+changeTimeFormat(appointment.time_end)"></span>
                                            <small class="text-slate-400" x-text="appointment.date"></small>
                                        </div>
                                    </td>
                                    <td class="column w-32">
                                        <template x-if="appointment.canceled_at && !appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <span class="badge badge-danger"><?php echo e(__('general.appointment.index.table.canceled')); ?></span>
                                        </template>
                                        <template x-if="appointment.confirmed_at && appointment.paid_at && appointment.recorded_at && !appointment.canceled_at">
                                            <span class="badge badge-info"><?php echo e(__('general.appointment.index.table.completed')); ?></span>
                                        </template>
                                        <template x-if="!appointment.canceled_at && appointment.confirmed_at && appointment.paid_at && !appointment.recorded_at">
                                            <span class="badge badge-success"><?php echo e(__('general.appointment.index.table.served and paid')); ?></span>
                                        </template>
                                        <template x-if="!appointment.canceled_at && appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <span class="badge badge-warning"><?php echo e(__('general.appointment.index.table.confirmed')); ?></span>
                                        </template>
                                        <template x-if="!appointment.canceled_at && !appointment.confirmed_at && !appointment.paid_at && !appointment.recorded_at">
                                            <span class="badge badge-neutral"><?php echo e(__('general.appointment.index.table.pending')); ?></span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </template>
                        <template x-if="!isLoading && appointmentList.length === 0">
                            <tr><td colspan="5" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    <span class="text-sm"><?php echo e(__('general.appointment.index.table.empty')); ?></span>
                                </div>
                            </td></tr>
                        </template>
                    </tbody>
                </table>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <section class="mt-4">
            <nav class="flex items-center justify-between bg-white rounded-2xl border border-slate-200 shadow-sm px-4 py-3">
                <div class="flex w-0 flex-1">
                    <template x-if="pagination.page > 1">
                        <button type="button" @click="handlePreviousPage()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-600 rounded-2xl border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
                            <span class="hidden md:block"><?php echo e(__('Previous')); ?></span>
                        </button>
                    </template>
                </div>

                <div class="hidden md:flex items-center gap-1.5">
                    <template x-if="pagination.page - 3 >= 0">
                        <button type="button" x-text="1"
                            class="w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200"
                            @click="pagination.page = 1; lookup()"></button>
                    </template>

                    <template x-if="pagination.page - 3 > 0">
                        <span class="px-1 text-slate-400">...</span>
                    </template>

                    <template x-for="page in pagination.last" :key="page">
                        <template x-if="page > (pagination.page - 2) && page < (pagination.page + 2)">
                            <button type="button" x-text="page"
                                :class="`${(pagination.page == page) ? 'min-w-[42px] h-10 flex items-center justify-center text-sm font-bold px-2 text-white cursor-pointer relative overflow-hidden' : 'w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200'}`"
                                :style="pagination.page == page ? 'background: radial-gradient(circle at 20% 20%, #165b38 0%, #0a331f 40%, #051d11 80%); box-shadow: inset 1.5px 2px 3px rgba(255,255,255,0.5), inset -2px -2.5px 4px rgba(0,0,0,0.8), 0 3px 8px rgba(5,29,17,0.45); border: 1px solid rgba(0,0,0,0.5); border-radius: 16px;' : ''"
                                @click="pagination.page = page; lookup()"></button>
                        </template>
                    </template>

                    <template x-if="pagination.page + 2 < pagination.last">
                        <span class="px-1 text-slate-400">...</span>
                    </template>

                    <template x-if="pagination.page + 2 <= pagination.last">
                        <button type="button" x-text="pagination.last"
                            class="w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200"
                            @click="pagination.page = pagination.last; lookup()"></button>
                    </template>
                </div>

                <div class="flex w-0 flex-1 justify-end">
                    <template x-if="pagination.page < pagination.last">
                        <button type="button" @click="handleNextPage()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-600 rounded-2xl border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
                            <span class="hidden md:block"><?php echo e(__('Next')); ?></span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                        </button>
                    </template>
                </div>
            </nav>
        </section>
    </main>

<?php if (! $__env->hasRenderedOnce('95977a10-aec7-43fa-9519-509bbba426c3')): $__env->markAsRenderedOnce('95977a10-aec7-43fa-9519-509bbba426c3');
$__env->startPush('scripts'); ?>
    <script type="text/javascript">
        function count(string) {
            const jsonObject = JSON.parse(string);
            return jsonObject.length;
        }

        function changeTimeFormat(time) {
            if (!time) return '-';
            const parts = time.split(':');
            return (parts[0] || '00').padStart(2, '0') + ':' + (parts[1] || '00').padStart(2, '0');
        }

        const appointmentLookup = {
            keyword: "",
            appointmentList: [],
            isLoading: false,
            pagination: {
                page: 1,
                limit: 20,
                last: 1,
                total: 1,
            },
            init() {
                this.lookup();
            },
            lookup() {
                this.isLoading = true;
                this.appointmentList = [];
                let params = new URLSearchParams({
                    keyword: this.keyword,
                    page: this.pagination.page,
                    limit: this.pagination.limit,
                });
                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|nurse')): ?>
                    fetch(`<?php echo e(route('api.appointments.lookup')); ?>?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.appointmentList = data.data;
                            this.pagination = {
                                ...this.pagination,
                                last: Math.max(1, data.pagination.last),
                                total: data.pagination.total,
                            };
                            this.isLoading = false;
                        })
                        .catch(() => { this.isLoading = false; });
                <?php endif; ?>
                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'doctor')): ?>
                    fetch(`<?php echo e(route('api.appointments.lookup.doctor')); ?>?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.appointmentList = data.data;
                            this.pagination = {
                                ...this.pagination,
                                last: Math.max(1, data.pagination.last),
                                total: data.pagination.total,
                            };
                            this.isLoading = false;
                        })
                        .catch(() => { this.isLoading = false; });
                <?php endif; ?>
            },
            handleNextPage() {
                this.pagination.page++;
                this.lookup();
            },
            handlePreviousPage() {
                this.pagination.page--;
                this.lookup();
            },
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
<?php /**PATH C:\laragon\www\katagigi-dignify\resources\views/pages/general/appointment/index.blade.php ENDPATH**/ ?>