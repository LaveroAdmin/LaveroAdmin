<div class="btn-group " style="margin-right: 5px">
    <label class="btn btn-sm btn-outline-secondary btn-filter {{ $btn_class }} {{ $expand ? '' : 'collapsed' }}" title="{{ trans('admin.filter') }}" data-bs-toggle="collapse" href="#{{ $filter_id }}" role="button" aria-expanded="false" aria-controls="{{ $filter_id }}">
        <i class="icon-filter"></i><span class="hidden-xs">&nbsp;&nbsp;{{ trans('admin.filter') }}</span>@if(!empty($active_count))<span class="badge bg-primary text-dark rounded-pill ms-1" style="font-size: 0.72rem; padding: 2px 6px;">{{ $active_count }}</span>@endif @if($scopes->isEmpty())<i class="icon-angle-down"></i>@endif<i class="icon-angle-up"></i>
    </label>

    @if($scopes->isNotEmpty())

    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
        <span>{{ $label }}</span>
        <span class="caret"></span>
        <span class="sr-only">{{ trans('admin.toggle_dropdown') }}</span>
    </button>
    <ul class="dropdown-menu">
        @foreach($scopes as $scope)
            {!! $scope->render() !!}
        @endforeach
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ $cancel }}">{{ trans('admin.cancel') }}</a></li>
    </ul>

    @endif

</div>