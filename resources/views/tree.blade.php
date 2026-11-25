<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group">
                <a class="btn btn-outline-secondary btn-sm {{ $id }}-tree-tools" data-action="expand" title="{{ trans('admin.expand') }}" onclick="admin.tree.expand();">
                    <i class="icon-plus-square"></i><span class="d-none d-sm-inline">&nbsp;{{ trans('admin.expand') }}</span>
                </a>
                <a class="btn btn-outline-secondary btn-sm {{ $id }}-tree-tools" data-action="collapse" title="{{ trans('admin.collapse') }}" onclick="admin.tree.collapse();">
                    <i class="icon-minus-square"></i><span class="d-none d-sm-inline">&nbsp;{{ trans('admin.collapse') }}</span>
                </a>
            </div>

            @if($useSave)
            <div class="btn-group">
                <a class="btn btn-primary btn-sm {{ $id }}-save" title="{{ trans('admin.save') }}" onclick="admin.tree.save();">
                    <i class="icon-save"></i><span class="d-none d-sm-inline">&nbsp;{{ trans('admin.save') }}</span>
                </a>
            </div>
            @endif

            @if($useRefresh)
            <div class="btn-group">
                <a class="btn btn-outline-secondary btn-sm {{ $id }}-refresh" title="{{ trans('admin.refresh') }}" onclick="admin.ajax.reload();">
                    <i class="icon-refresh"></i><span class="d-none d-sm-inline">&nbsp;{{ trans('admin.refresh') }}</span>
                </a>
            </div>
            @endif

            <div class="btn-group">
                {!! $tools !!}
            </div>
        </div>

        @if($useCreate)
        <div class="d-flex align-items-center ms-auto">
            <a class="btn btn-success btn-sm text-white" href="{{ url($path) }}/create">
                <i class="icon-plus"></i><span class="d-none d-sm-inline">&nbsp;{{ trans('admin.new') }}</span>
            </a>
        </div>
        @endif

    </div>
    <!-- /.box-header -->
    <div class="card-body table-responsive no-padding">
        <div class="dd" id="{{ $id }}">
            <ol class="dd-list">
                @each($branchView, $items, 'branch')
            </ol>
        </div>
    </div>
    <!-- /.box-body -->
</div>
