<div class="with-border collapse {{ $expand?'show':'' }} filter-box" id="{{ $filterID }}">
    @if(!empty($active_filters))
    <div class="active-filters-chips px-3 py-2 border-bottom bg-light d-flex flex-wrap align-items-center gap-2">
        <span class="small text-muted fw-semibold"><i class="icon-filter"></i> {{ __('admin.active_filters') }}:</span>
        @foreach($active_filters as $item)
            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.8rem;">
                <span class="text-muted">{{ $item['label'] }}:</span>
                <strong>{{ $item['value'] }}</strong>
                <a href="{{ $item['url_without'] }}" class="text-danger ms-1 text-decoration-none" title="{{ __('admin.remove') }}">
                    <i class="icon-times"></i>
                </a>
            </span>
        @endforeach
        <a href="{!! $action !!}" class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-none py-1 px-2" style="font-size: 0.8rem;">
            <i class="icon-undo"></i> {{ __('admin.clear_all') }}
        </a>
    </div>
    @endif
    <form action="{!! $action !!}" class="form pt-0 form-horizontal" pjax-container method="get" autocomplete="off">

        <div class="row mb-0">
            @foreach($layout->columns() as $column)
            <div class="col-md-{{ $column->width() }}">
                <div class="card-body">
                    <div class="fields-group">
                        @foreach($column->filters() as $filter)
                            {!! $filter->render() !!}
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <!-- /.box-body -->

        <div class="card-footer">
            <div class="row">
                <div class="col-md-{{ $layout->columns()->first()->width() }}">
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <div class="btn-group pull-left">
                                <button class="btn btn-primary submit btn-sm"><i
                                            class="icon-search"></i>&nbsp;&nbsp;{{ trans('admin.search') }}</button>
                            </div>
                            <div class="btn-group pull-left " style="margin-left: 10px;">
                                <a href="{!! $action !!}" class="btn btn-light btn-sm"><i
                                            class="icon-undo"></i>&nbsp;&nbsp;{{ trans('admin.reset') }}</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>
