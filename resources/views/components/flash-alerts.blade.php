@if (Session::has('success'))
    <div class="mb-8"><x-alerts.success message="{{ Session::get('success') }}" /></div>
@endif

@if (Session::has('error'))
    <div class="mb-8"><x-alerts.failed message="{{ Session::get('error') }}" /></div>
@endif
