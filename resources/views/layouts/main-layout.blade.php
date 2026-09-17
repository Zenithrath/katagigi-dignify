<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--<meta http-equiv="Content-Security-Policy" content="default-src 'self' https://app.oktagriyagigi.com/ blob: data:; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://unpkg.com/ blob: data:; connect-src self * 'unsafe-inline' blob: data: gap:; img-src 'self' blob: data:; style-src 'self' 'unsafe-inline'; font-src 'self' data:; worker-src https://unpkg.com  blob: data:;">-->

    {{-- <meta http-equiv="Content-Security-Policy"
        content="default-src 'self' https://unpkg.com/ https://api.qrserver.com file: data: blob:
            filesystem:; style-src 'self' 'unsafe-inline';
            img-src 'self' https://api.qrserver.com/ blob: data: 'unsafe-inline' 'unsafe-eval';
            script-src 'self' https://unpkg.com/ 'unsafe-inline' 'unsafe-eval'" /> --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <link rel="shortcut icon" href="/favicon.svg" type="image/x-icon" />
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
    <title>@yield('_title') - {{ __('Kata Gigi') }}</title>
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
</head>

<body x-data>
    @yield('navigator')

    <div class="content-base">
        @yield('header')
        @yield('content')
        @yield('footer')
    </div>

    <div class="print-base">
        @yield('printable')
    </div>

    @stack('scripts')

    <script lang="text/javascript">
        document.addEventListener("alpine:init", function() {
            Alpine.store('sidenavExpanded', {
                isExpanded: true,
                init() {
                    let state = localStorage.getItem("isSidenavExpanded") ?? "false";
                    this.isExpanded = state === "true";
                },
                setIsExpanded() {
                    this.isExpanded = !this.isExpanded;
                    localStorage.setItem("isSidenavExpanded", this.isExpanded);
                }
            });
        });
    </script>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', () => {
            const elems = document.getElementsByClassName('selectable');
            for (let index = 0; index < elems.length; index++) {
                initSelectable(elems[index]);
            }
        });

        function initSelectable(element) {
            $(element).select2({
                width: '100%',
                id: element.getAttribute('id'),
                dropdownParent: $(element).parent()
            });
        }
    </script>
</body>

</html>
