<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
        @if(config('admin.show_environment'))
            <strong>{{ trans('admin.env') }}</strong>&nbsp;&nbsp; {!! config('app.env') !!}
        @endif

        &nbsp;&nbsp;&nbsp;&nbsp;

        @if(config('admin.show_version'))
        <strong>{{ trans('admin.version') }}</strong>&nbsp;&nbsp; {!! \LaveroAdmin\Admin\Admin::VERSION !!}
        @endif
    </div>
    <!-- Default to the left -->
    <strong>{{ trans('admin.powered_by') }} <a href="https://github.com/LaveroAdmin/LaveroAdmin" target="_blank">LaveroAdmin</a></strong>
</footer>