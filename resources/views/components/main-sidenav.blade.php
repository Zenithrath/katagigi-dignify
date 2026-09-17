<aside x-data :class="$store.sidenavExpanded.isExpanded ? 'sidenav' : 'sidenav-collapsed'" x-transition.duration-300ms>
    <div class="flex items-center justify-between lg:justify-center mb-8 w-full">
        <div x-data class="flex items-center gap-4" :class="$store.sidenavExpanded.isExpanded ? null : 'justify-center'">
            <img src="/assets/logo.svg" alt="" class="w-fit h-8" />
        </div>
        <button class="clickable-ghost icon-only md:hidden" id="sidenav-toggler" x-data
            @click="$store.sidenavExpanded.setIsExpanded()">
            <template x-if="$store.sidenavExpanded.isExpanded">
                <x-icons.playlist-x />
            </template>
            <template x-if="!$store.sidenavExpanded.isExpanded">
                <x-icons.menu2 />
            </template>
        </button>
    </div>

    <div class="item-container">
        <section>
            <span x-data
                :class="$store.sidenavExpanded.isExpanded ? null : 'collapsed'">{{ __('navigation.sidenav.general._title') }}</span>
            <a href="{{ route('dashboard') }}"
                class="{{ $attributes['feature'] === 'GENERAL.HOME' ? 'active' : '' }}">
                <div class="w-4 h-4">
                    <x-icons.home />
                </div>
                <span x-data
                    x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.general.home') }}</span>
            </a>
            @can('read schedule')
                <a href="{{ route('schedules.index') }}"
                    class="{{ $attributes['feature'] === 'GENERAL.SCHEDULE' ? 'active' : '' }}">
                    <div class="w-4 h-4">
                        <x-icons.clock />
                    </div>
                    <span x-data
                        x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.general.schedule') }}</span>
                </a>
            @endcan

            @can('read appointment')
                <a href="{{ route('appointments.index') }}"
                    class="{{ $attributes['feature'] === 'GENERAL.APPOINTMENT' ? 'active' : '' }}">
                    <div class="w-4 h-4">
                        <x-icons.ad2 />
                    </div>
                    <span x-data
                        x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.general.appointment') }}</span>
                </a>
            @endcan

            @can('read service')
                <a href="{{ route('services.index') }}"
                    class="{{ $attributes['feature'] === 'GENERAL.SERVICE' ? 'active' : '' }}">
                    <div class="w-4 h-4">
                        <x-icons.notebook />
                    </div>
                    <span x-data
                        x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.general.service') }}</span>
                </a>
            @endcan
        </section>

        <section>
            <span x-data
                :class="$store.sidenavExpanded.isExpanded ? null : 'collapsed'">{{ __('navigation.sidenav.patient._title') }}</span>
            @can('read patient')
                <a href="{{ route('patients.index') }}"
                    class="{{ $attributes['feature'] === 'PATIENT.MASTER' ? 'active' : '' }}">
                    <div class="w-4 h-4">
                        <x-icons.wheelchair />
                    </div>
                    <span x-data
                        x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.patient.master') }}</span>
                </a>
            @endcan

            @can('read medical record')
                <a href="{{ route('medical-records.index') }}"
                    class="{{ $attributes['feature'] === 'PATIENT.RECORD' ? 'active' : '' }}">
                    <div class="w-4 h-4">
                        <x-icons.checkup-list />
                    </div>
                    <span x-data
                        x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.patient.record') }}</span>
                </a>
            @endcan
        </section>

        @role('manajemen|admin')
            <section>
                <span x-data
                    :class="$store.sidenavExpanded.isExpanded ? null : 'collapsed'">{{ __('navigation.sidenav.employmentship._title') }}</span>

                @can('read doctor')
                    <a href="{{ route('doctors.index') }}"
                        class="{{ $attributes['feature'] === 'MASTER.DOCTOR' ? 'active' : '' }}">
                        <div class="w-4 h-4">
                            <x-icons.stethoscope />
                        </div>
                        <span x-data
                            x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.employmentship.doctor') }}</span>
                    </a>
                @endcan

                @can('read nurse')
                    <a href="{{ route('nurses.index') }}"
                        class="{{ $attributes['feature'] === 'MASTER.NURSE' ? 'active' : '' }}">
                        <div class="w-4 h-4">
                            <x-icons.nurse />
                        </div>
                        <span x-data
                            x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.employmentship.nurse') }}</span>
                    </a>
                @endcan

                @can('read admin')
                    <a href="{{ route('admins.index') }}"
                        class="{{ $attributes['feature'] === 'MASTER.ADMIN' ? 'active' : '' }}">
                        <div class="w-4 h-4">
                            <x-icons.user-cog />
                        </div>
                        <span x-data
                            x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.employmentship.admin') }}</span>
                    </a>
                @endcan
            </section>
        @endrole

        <section>
            <span x-data
                :class="$store.sidenavExpanded.isExpanded ? null : 'collapsed'">{{ __('navigation.sidenav.report._title') }}</span>

            @role('manajemen|admin|nurse')
                <a href="{{ route('installments.index') }}"
                    class="{{ $attributes['feature'] === 'REPORT.INSTALLMENT' ? 'active' : '' }}">
                    <div class="w-4 h-4">
                        <x-icons.file-invoice />
                    </div>
                    <span x-data
                        x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.report.installment') }}</span>
                </a>
            @endrole

            @can('read transaction')
                <a href="{{ route('transactions.index') }}"
                    class="{{ $attributes['feature'] === 'REPORT.TRANSACTION' ? 'active' : '' }}">
                    <div class="w-4 h-4">
                        <x-icons.arrows-left-right />
                    </div>
                    <span x-data
                        x-show="$store.sidenavExpanded.isExpanded">{{ __('navigation.sidenav.report.transaction') }}</span>
                </a>
            @endcan

            @can('read turnover')
                <a href="{{ route('incomes.index') }}"
                    class="{{ $attributes['feature'] === 'REPORT.TURNOVER' ? 'active' : '' }}">
                    <div class="w-4 h-4">
                        <x-icons.report />
                    </div>
                    <span x-data x-show="$store.sidenavExpanded.isExpanded">{{ __('Income') }}</span>
                </a>
            @endcan
        </section>
    </div>
</aside>

<div x-show="$store.sidenavExpanded.isExpanded"
    @click="$store.sidenavExpanded.setIsExpanded(!$store.sidenavExpanded.isExpanded)"
    class="fixed top-0 right-0 z-30 w-screen h-screen bg-slate-300/30 dark:bg-slate-700/30 backdrop-blur-sm md:hidden">
</div>
