<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Admin::title() }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @if(!is_null($favicon = Admin::favicon()))
    <link rel="shortcut icon" href="{{$favicon}}">
    @endif

    {!! Admin::css() !!}
    {!! Admin::headerJs() !!}
    {!! Admin::js() !!}
    {!! Admin::js_trans() !!}

</head>

@php
    $layoutOptions = (array) config('admin.layout', []);
    $layoutClasses = implode(' ', array_filter($layoutOptions));
    if (in_array('sidebar-collapse', $layoutOptions, true)) {
        $layoutClasses .= ' side-menu-closed';
    }
@endphp

<body class="{{ config('admin.skin') }} {{ trim($layoutClasses . ' ' . $body_classes) }}">

    <div class="wrapper">

        @include('admin::partials.header')
        @include('admin::partials.sidebar')
        <div id="sidebar-backdrop" class="sidebar-backdrop"></div>
        <main id="main" class="p-4">

            @if($alert = config('admin.top_alert'))
                <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                    {!! $alert !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ trans('admin.close') }}"></button>
                </div>
            @endif

            <div id="pjax-container">
            <!--start-pjax-container-->
                {!! Admin::style() !!}
                <div id="app">
                    @yield('content')
                </div>
                {!! Admin::html() !!}
                {!! Admin::script() !!}
            <!--end-pjax-container-->
            </div>

        </main>
    </div>

    @if (1==2)
        @include('admin::partials.footer')
    @endif

    <button id="totop" title="{{ trans('admin.go_to_top') }}" style="display: none;"><i class="icon-chevron-up"></i></button>

    <script>
        function LA() {}
        LA.token = "{{ csrf_token() }}";
        LA.user = @json($_user_);
    </script>

    </body>
</html>
