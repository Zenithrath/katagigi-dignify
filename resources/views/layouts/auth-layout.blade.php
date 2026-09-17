<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="shortcut icon" href="/favicon.svg" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <title>@yield('_title') - {{ __('Kata Gigi') }}</title>
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
</head>

<body>
    <div class="auth-base-isolation">
        <div class="content-base auth-base">
            @yield('content')
            <x-main-footer />
        </div>

        <div class="auth-accent-container">
            <div class="auth-accent"
                style="clip-path:polygon(100% 38.5%, 82.6% 100%, 60.2% 37.7%, 52.4% 32.1%, 47.5% 41.8%, 45.2% 65.6%, 27.5% 23.4%, 0.1% 35.3%, 17.9% 0%, 27.7% 23.4%, 76.2% 2.5%, 74.2% 56%, 100% 38.5%)">
            </div>
        </div>
    </div>


    @stack('scripts')
</body>

</html>
