<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="@stack('html-class')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Scripts --}}
    @stack('head-scripts')
    <script src="{{ mix('js/app.js') }}" defer></script>

    {{-- Fonts --}}
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    {{-- Styles --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    @stack('head-after')
</head>
<body>

<div id="app">
    @auth
        @php
            // Get user's roles and permissions
            $user = 'null';

            if (Auth::id()) {
                $user = \App\User::getUserWithRolesAndPermissions(Auth::id());
            }
        @endphp

    <!-- Progress bar -->
    <vue-progress-bar></vue-progress-bar>

    <!-- Navbar -->
    <navbar :user="{{ $user }}"></navbar>

    <!-- User's view -->
    <router-view :user="{{ $user }}"></router-view>
    @endauth
</div>

@guest
    <main style="background-color: whitesmoke;">
        @yield('content')
    </main>
@endguest

@stack('bottom')

</body>
</html>
