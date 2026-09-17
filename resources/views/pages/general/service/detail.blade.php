<x-app-layout>
    <x-slot:title>{{ __('general.service.detail._title') }}</x-slot:title>

    <main class="mb-auto px-8 pt-8 pb-12">
        <div class="flex gap-4 items-center">
            <a href="{{ route('services.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-lucide-chevron-left class="w-full h-full" />
            </a>
            <h1> {{ __('general.service.detail._title') }} </h1>
        </div>

        <section id="detail" class="mt-4 content-card p-8">
            <dl class="detail-list">
                <div class="data-container">
                    <dt>{{ __('general.service.detail.labels.name') }}</dt>
                    <dd>{{ $data->name }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.service.detail.labels.category') }}</dt>
                    <dd>{{ $data->category_name ?? '-' }}</dd>

                </div>
                <div class="data-container">
                    <dt>{{ __('general.service.detail.labels.lower_price') }}</dt>
                    <dd>{{ $toRupiah($data->lower_price) }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.service.detail.labels.upper_price') }}</dt>
                    <dd>{{ $toRupiah($data->upper_price) }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.service.detail.labels.commision') }}</dt>
                    <dd>{{ $data->doctor_commision }}% -- &plusmn;
                        {{ $toRupiah(($data->lower_price / 100) * $data->doctor_commision) }} &dash;
                        {{ $toRupiah(($data->upper_price / 100) * $data->doctor_commision) }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.service.detail.labels.description') }}</dt>
                    <dd>{{ $data->description }}</dd>
                </div>
            </dl>
        </section>
    </main>


@pushOnce('scripts')
    <script type="text/javascript">
        function copyEmail() {
            let email = document.getElementById('email');
            let payment_email = document.getElementById('payment_email');
            payment_email.value = email.value;
        }
    </script>
@endPushOnce
</x-app-layout>
