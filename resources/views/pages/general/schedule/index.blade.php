@extends('layouts.main-layout')

@section('_title', 'Dashboard')
@section('header')
    <x-main-header title="{{ __('features.schedule') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="GENERAL.SCHEDULE" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="main-table-container" x-data="scheduleLookup">
        <section class="heading">
            <div>
                <h1>{{ __('general.schedule.index.title') }}</h1>
                <p>{{ __('general.schedule.index.subtitle') }}</p>
            </div>
        </section>

        @if (Session::has('success'))
            <div class="mb-8">
                <x-alerts.success message="{{ Session::get('success') }}" />
            </div>
        @endif

        @if (Session::has('error'))
            <div class="mb-8">
                <x-alerts.failed message="{{ Session::get('error') }}" />
            </div>
        @endif

        <section class="py-2 px-4 mt-4">
            <span x-text="`Found: ${pagination.total} entries.`"></span>
        </section>

        @role('admin|nurse')
            <div class="content-card">
                <h2 class="mb-5">{{ __('general.schedule.form.title._title') }}</h2>
                <form method="post" action="{{ route('schedules.store') }}" id="schedule-form">
                    @csrf
                    <div class="input-container">
                        <div class="flex flex-col md:flex-row gap-2">
                            <div class="flex-1">
                                <div class="input-group">
                                    <label for="doctor"
                                        class="input-label">{{ __('general.schedule.form.labels.doctor._title') }}</label>
                                    <select name="doctor_id" id="doctor" class="selectable">
                                        <option selected disabled>
                                            {{ __('general.schedule.form.labels.doctor.default') }}
                                        </option>
                                        @if (count($doctorList) == 0)
                                            <option disabled>
                                                {{ __('general.schedule.form.labels.doctor.empty') }}
                                            </option>
                                        @else
                                            @foreach ($doctorList as $doctor)
                                                <option value="{{ $doctor->id }}">
                                                    {{ $doctor->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('doctor_id')
                                        <small class="danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            @can('create schedule')
                                <div class="flex-1">
                                    <div class="input-group">
                                        <label for="day"
                                            class="input-label">{{ __('general.schedule.form.labels.day._title') }}</label>
                                        <select name="day" id="day" autocomplete="day" class="h-10">
                                            <option selected disabled>
                                                {{ __('general.schedule.form.labels.day.default') }}
                                            </option>
                                            <option value="MONDAY">
                                                {{ __('general.schedule.form.labels.day.monday') }}
                                            </option>
                                            <option value="TUESDAY">
                                                {{ __('general.schedule.form.labels.day.tuesday') }}
                                            </option>
                                            <option value="WEDNESDAY">
                                                {{ __('general.schedule.form.labels.day.wednesday') }}
                                            </option>
                                            <option value="THURSDAY">
                                                {{ __('general.schedule.form.labels.day.thursday') }}
                                            </option>
                                            <option value="FRIDAY">
                                                {{ __('general.schedule.form.labels.day.friday') }}
                                            </option>
                                            <option value="SATURDAY">
                                                {{ __('general.schedule.form.labels.day.saturday') }}
                                            </option>
                                            <option value="SUNDAY">
                                                {{ __('general.schedule.form.labels.day.sunday') }}
                                            </option>
                                        </select>
                                        @error('day')
                                            <small class="danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="flex-1 flex gap-2">
                                    <div class="input-group flex-1">
                                        <label for="start_time"
                                            class="input-label">{{ __('general.schedule.form.labels.start_time') }}</label>
                                        <input type="time" name="start_time" id="start_time" class="" value="" />
                                        @error('start_time')
                                            <small class="danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="input-group flex-1">
                                        <label for="end_time"
                                            class="input-label">{{ __('general.schedule.form.labels.end_time') }}</label>
                                        <input type="time" name="end_time" id="end_time" class="" value="" />
                                        @error('end_time')
                                            <small class="danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="flex items-end mt-2 md:mt-0">
                                    <input type="submit" value="{{ __('general.schedule.form.buttons.add') }}"
                                        class="clickable-primary px-4 py-2 mb-1 rounded-md flex-1" />
                                </div>
                            @endcan
                        </div>
                    </div>
                </form>
            </div>
        @endrole

        @role('doctor')
            <section class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="column">{{ __('No.') }}</th>
                            <th scope="col" class="index-column">{{ __('general.schedule.index.table.doctor') }}</th>
                            <th scope="col" class="column">{{ __('general.schedule.index.table.day') }}</th>
                            <th scope="col" class="column">{{ __('general.schedule.index.table.working_time') }}</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <template x-if="scheduleList.length > 0 && !isLoading">
                            <template x-for="(schedule, index) in scheduleList" :key="schedule.id">
                                <tr>
                                    <td class="column" x-text="(pagination.page - 1) * pagination.limit + index + 1"></td>
                                    <td class="column" x-text="schedule.name"></td>
                                    <td class="column" x-text="ucfirst(schedule.day)"></td>
                                    <td class="column">
                                        <template x-if="schedule.time_start">
                                            <span x-text="formatTime(schedule.time_start)"></span>
                                        </template>
                                        -
                                        <template x-if="schedule.time_end">
                                            <span x-text="formatTime(schedule.time_end)"></span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </section>
        @endrole

        @role('admin|nurse')
            <section class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="column">{{ __('No.') }}</th>
                            <th scope="col" class="index-column">{{ __('general.schedule.index.table.doctor') }}</th>
                            <th scope="col" class="column">{{ __('general.schedule.index.table.day') }}</th>
                            <th scope="col" class="column">{{ __('general.schedule.index.table.working_time') }}</th>
                            <th scope="col" class="action-column">
                                <span class="sr-only"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <template x-if="scheduleList.length > 0 && !isLoading">
                            <template x-for="(schedule, index) in scheduleList" :key="schedule.id">
                                <tr>
                                    <td class="column" x-text="(pagination.page - 1) * pagination.limit + index + 1"></td>
                                    <td class="column" x-text="schedule.name"></td>
                                    <td class="column" x-text="ucfirst(schedule.day)"></td>
                                    <td class="column">
                                        <template x-if="schedule.time_start">
                                            <span x-text="formatTime(schedule.time_start)"></span>
                                        </template>
                                        -
                                        <template x-if="schedule.time_end">
                                            <span x-text="formatTime(schedule.time_end)"></span>
                                        </template>
                                    </td>
                                    <td class="action-column">
                                        <div class="flex gap-2">
                                            @role('nurse')
                                                <button class="text-success-600 hover:text-success-500 active:text-success-700"
                                                    type="submit" x-text="ucfirst(schedule.availability)"><span
                                                        class="sr-only"></span></button>
                                            @endrole

                                            @can('update schedule')
                                                <form
                                                    :action="(
                                                        `{{ route('schedules.update_status', ['schedule' => 'schedule.id']) }}`
                                                    )
                                                    .replace('schedule.id', schedule.id)"
                                                    method="post">
                                                    @csrf

                                                    <template x-if="schedule.availability == 'AVAILABLE'">
                                                        <button
                                                            class="text-success-600 hover:text-success-500 active:text-success-700"
                                                            type="submit" x-text="ucfirst(schedule.availability)"><span
                                                                class="sr-only"></span></button>
                                                    </template>
                                                    <template x-if="schedule.availability != 'AVAILABLE'">
                                                        <button
                                                            class="text-warning-600 hover:text-warning-500 active:text-warning-700"
                                                            type="submit" x-text="ucfirst(schedule.availability)"><span
                                                                class="sr-only"></span></button>
                                                    </template>
                                                </form>
                                            @endcan

                                            @can('delete schedule')
                                                <form
                                                    :action="(`{{ route('schedules.destroy', ['schedule' => 'schedule.id']) }}`)
                                                    .replace('schedule.id', schedule.id)"
                                                    method="post">
                                                    @csrf
                                                    @method('delete')

                                                    <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                        type="submit">{{ __('general.schedule.index.buttons.delete') }}<span
                                                            class="sr-only"></span></button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </section>
        @endrole

        <section class="mt-4">
            <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 sm:px-0">
                <div class="-mt-px flex w-0 flex-1">
                    <template x-if="pagination.page > 1">
                        <button type="button" @click="handlePreviousPage()"
                            class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M18 10a.75.75 0 01-.75.75H4.66l2.1 1.95a.75.75 0 11-1.02 1.1l-3.5-3.25a.75.75 0 010-1.1l3.5-3.25a.75.75 0 111.02 1.1l-2.1 1.95h12.59A.75.75 0 0118 10z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="hidden md:block">
                                {{ __('Previous') }}
                            </span>
                        </button>
                    </template>
                </div>

                <div class="hidden md:-mt-px md:flex">
                    <template x-if="pagination.page - 3 >= 0">
                        <button type="button" x-text="1"
                            :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800`"
                            @click="pagination.page = 1; lookup()"></button>
                    </template>

                    <template x-if="pagination.page - 3 > 0">
                        <div
                            :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800`">
                            ...</div>
                    </template>

                    <template x-for="page in pagination.last" :key="page">
                        <template x-if="page > (pagination.page - 2) && page < (pagination.page + 2)">
                            <button type="button" x-text="page"
                                :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md hover:bg-gray-50 dark:hover:bg-gray-800 ${(pagination.page == page) ? 'text-indigo-600 border-t-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700'}`"
                                @click="pagination.page = page; lookup()"></button>
                        </template>
                    </template>

                    <template x-if="pagination.page + 2 < pagination.last">
                        <div
                            :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800`">
                            ...</div>
                    </template>

                    <template x-if="pagination.page + 2 <= pagination.last">
                        <button type="button" x-text="pagination.last"
                            :class="`mx-1 inline-flex items-center px-4 py-2 text-sm font-medium rounded-b-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800`"
                            @click="pagination.page = pagination.last; lookup()"></button>
                    </template>
                </div>

                <div class="-mt-px flex w-0 flex-1 justify-end">
                    <template x-if="pagination.page < pagination.last">
                        <button type="button" @click="handleNextPage()"
                            class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <span class="hidden md:block">
                                {{ __('Next') }}
                            </span>
                            <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z"
                                    clip-rule="evenodd" />s
                            </svg>
                        </button>
                    </template>
                </div>
            </nav>
        </section>
    </main>
@endsection

@pushOnce('scripts')
    <script type="text/javascript">
        let doctor_id = null;
        let day = null;

        let url = "{{ route('schedules.filter') }}";
        const body = document.getElementById('table-body');
        @role('admin|nurse')
            document.getElementById('doctor').addEventListener('change', function() {
                doctor_id = this.value;
                let params = {
                    doctor_id: doctor_id
                }
                let url_query = new URLSearchParams(params).toString();
                body.innerHTML = '';
                getData(url + '?' + url_query)
                    .then(data => {
                        data.map((item) => {
                            let tr = document.createElement('tr');

                            let tdName = document.createElement('td');
                            tdName.classList.add('column');
                            tdName.innerText = item.name;

                            let tdDay = document.createElement('td');
                            tdDay.classList.add('column');
                            tdDay.innerText = ucwords(item.day);

                            let tdStartTime = document.createElement('td');
                            tdStartTime.classList.add('column');
                            let stringTime = item.time_start ? changeTimeFormat(item.time_start) : '-';
                            stringTime += item.time_end ? ' - ' + changeTimeFormat(item.time_end) : '-';
                            tdStartTime.innerText = stringTime;

                            let tdAction = document.createElement('td');
                            tdAction.classList.add('action-column');

                            let div = document.createElement('div');
                            div.classList.add('flex', 'gap-2');

                            let formStatus = document.createElement('form');
                            formStatus.setAttribute('action', '/schedules/update_status' + '/' + item
                                .id);
                            formStatus.setAttribute('method', 'post');

                            let csrfStatus = document.createElement('input');
                            csrfStatus.setAttribute('type', 'hidden');
                            csrfStatus.setAttribute('name', '_token');
                            csrfStatus.setAttribute('value', '{{ csrf_token() }}');

                            let buttonStatus = document.createElement('button');

                            if (item.availability == 'AVAILABLE')
                                buttonStatus.classList.add('text-success-600', 'hover:text-success-500',
                                    'active:text-success-700');
                            else
                                buttonStatus.classList.add('text-warning-600', 'hover:text-warning-500',
                                    'active:text-warning-700');

                            buttonStatus.setAttribute('type', 'submit');

                            if (item.availability == 'AVAILABLE')
                                buttonStatus.innerText =
                                `{{ __('general.schedule.index.buttons.available') }}`;
                            else
                                buttonStatus.innerText =
                                `{{ __('general.schedule.index.buttons.unavailable') }}`;

                            let buttonStatusDefault = document.createElement('button');

                            if (item.availability == 'AVAILABLE')
                                buttonStatusDefault.classList.add('text-success-600',
                                    'hover:text-success-500',
                                    'active:text-success-700');
                            else
                                buttonStatusDefault.classList.add('text-warning-600',
                                    'hover:text-warning-500',
                                    'active:text-warning-700');

                            buttonStatusDefault.setAttribute('type', 'button');

                            if (item.availability == 'AVAILABLE')
                                buttonStatusDefault.innerText =
                                `{{ __('general.schedule.index.buttons.available') }}`;
                            else
                                buttonStatusDefault.innerText =
                                `{{ __('general.schedule.index.buttons.unavailable') }}`;

                            let spanStatus = document.createElement('span');
                            spanStatus.classList.add('sr-only');
                            spanStatus.innerText = item.name;

                            let spanStatusDefault = document.createElement('span');
                            spanStatusDefault.classList.add('sr-only');
                            spanStatusDefault.innerText = item.name;

                            let formDelete = document.createElement('form');
                            formDelete.setAttribute('action', '/schedules/' + item.id);
                            formDelete.setAttribute('method', 'post');

                            let csrfDelete = document.createElement('input');
                            csrfDelete.setAttribute('type', 'hidden');
                            csrfDelete.setAttribute('name', '_token');
                            csrfDelete.setAttribute('value', '{{ csrf_token() }}');

                            let methodDelete = document.createElement('input');
                            methodDelete.setAttribute('type', 'hidden');
                            methodDelete.setAttribute('name', '_method');
                            methodDelete.setAttribute('value', 'delete');

                            let buttonDelete = document.createElement('button');
                            buttonDelete.classList.add('text-danger-600', 'hover:text-danger-500',
                                'active:text-danger-700');
                            buttonDelete.setAttribute('type', 'submit');
                            buttonDelete.innerText =
                                `{{ __('general.schedule.index.buttons.delete') }}`;

                            let spanDelete = document.createElement('span');
                            spanDelete.classList.add('sr-only');
                            spanDelete.innerText = item.name;

                            @role('nurse')
                                buttonStatusDefault.appendChild(spanStatusDefault);
                                div.appendChild(buttonStatusDefault);
                            @endrole

                            @can('update schedule')
                                buttonStatus.appendChild(spanStatus);
                                formStatus.appendChild(csrfStatus);
                                formStatus.appendChild(buttonStatus);
                                div.appendChild(formStatus);
                            @endcan

                            @can('delete schedule')
                                buttonDelete.appendChild(spanDelete);
                                formDelete.appendChild(csrfDelete);
                                formDelete.appendChild(methodDelete);
                                formDelete.appendChild(buttonDelete);
                                div.appendChild(formDelete);
                            @endcan

                            tdAction.appendChild(div);

                            tr.appendChild(tdName);
                            tr.appendChild(tdDay);
                            tr.appendChild(tdStartTime);
                            tr.appendChild(tdAction);

                            body.appendChild(tr);
                        });
                    })
                    .catch(error => {
                        console.error(error);
                    })
            });
        @endrole

        let headers = new Headers({
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        });

        async function getData(url = "", data = {}) {
            const response = await fetch(url, {
                method: "POST",
                headers: headers,
            });
            return response.json();
        }

        function ucwords(str) {
            // Split the string into an array of words
            let words = str.toLowerCase().split(' ');

            // Capitalize the first letter of each word
            for (let i = 0; i < words.length; i++) {
                let word = words[i];
                words[i] = word.charAt(0).toUpperCase() + word.slice(1);
            }

            // Join the words back into a string
            let result = words.join(' ');

            return result;
        }

        function changeTimeFormat(time) {
            var parts = time.split(':');
            var hours = parts[0];
            var minutes = parts[1];

            var formattedTime = hours + ':' + minutes;
            return formattedTime;
        }

        const scheduleLookup = {
            keyword: "",
            scheduleList: [],
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
                this.scheduleList = [];
                let params = new URLSearchParams({
                    keyword: this.keyword,
                    page: this.pagination.page,
                    limit: this.pagination.limit,
                });
                @role('admin|nurse')
                    fetch(`{{ route('api.schedules.lookup') }}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.scheduleList = data.data;
                            this.pagination = {
                                ...this.pagination,
                                last: data.pagination.last,
                                total: data.pagination.total,
                            };
                            this.isLoading = false;
                        })
                @endrole

                @role('doctor')
                    fetch(`{{ route('api.schedules.lookup.doctor') }}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.scheduleList = data.data;
                            this.pagination = {
                                ...this.pagination,
                                last: data.pagination.last,
                                total: data.pagination.total,
                            };
                            this.isLoading = false;
                        })
                @endrole
            },
            handleNextPage() {
                this.pagination.page++;
                this.lookup();
            },
            handlePreviousPage() {
                this.pagination.page--;
                this.lookup();
            },
            formatTime(timeString) {
                const [hours, minutes] = timeString.split(':');
                const formattedHours = hours.padStart(2, '0');
                const formattedMinutes = minutes.padStart(2, '0');
                return `${formattedHours}:${formattedMinutes}`;
            },
            ucfirst(text) {
                const lowercaseText = text.toLowerCase();
                const firstLetter = lowercaseText.charAt(0);
                const remainingText = lowercaseText.slice(1);

                return firstLetter.toUpperCase() + remainingText;
            },
        }
    </script>
@endPushOnce
