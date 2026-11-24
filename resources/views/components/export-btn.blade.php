<div class="btn-group me-1">
    <a href="{{$grid->getExportUrl('all')}}" target="_blank" class="btn btn-sm btn-outline-secondary" title="{{trans('admin.export')}}"><i class="icon-download"></i><span class="hidden-xs"> {{trans('admin.export')}}</span></a>
    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="caret"></span>
        <span class="visually-hidden">{{ trans('admin.toggle_dropdown') }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li class="dropdown-header text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">{{ trans('admin.export') }} CSV</li>
        <li><a class="dropdown-item" href="{{$grid->getExportUrl('all')}}" target="_blank"><i class="icon-file-text-o text-secondary me-2"></i>{{trans('admin.all')}}</a></li>
        <li><a class="dropdown-item" href="{{$grid->getExportUrl('page', $page)}}" target="_blank"><i class="icon-file-text-o text-secondary me-2"></i>{{trans('admin.current_page')}}</a></li>
        <li><a class="dropdown-item" href="{{$grid->getExportUrl('selected', '__rows__')}}" target="_blank" onclick="admin.grid.export_selected_row(event);" data-no_rows_selected="{{__('admin.no_rows_selected')}}"><i class="icon-file-text-o text-secondary me-2"></i>{{trans('admin.selected_rows')}}</a></li>
        @if(class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet'))
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header text-uppercase fw-bold text-success" style="font-size: 0.72rem; letter-spacing: 0.05em;"><i class="icon-file-excel-o"></i> {{ trans('admin.export') }} Excel (.xlsx)</li>
            <li><a class="dropdown-item" href="{{$grid->getExportUrl('all', null, 'xlsx')}}" target="_blank"><i class="icon-file-excel-o text-success me-2"></i>{{trans('admin.all')}}</a></li>
            <li><a class="dropdown-item" href="{{$grid->getExportUrl('page', $page, 'xlsx')}}" target="_blank"><i class="icon-file-excel-o text-success me-2"></i>{{trans('admin.current_page')}}</a></li>
            <li><a class="dropdown-item" href="{{$grid->getExportUrl('selected', '__rows__', 'xlsx')}}" target="_blank" onclick="admin.grid.export_selected_row(event);" data-no_rows_selected="{{__('admin.no_rows_selected')}}"><i class="icon-file-excel-o text-success me-2"></i>{{trans('admin.selected_rows')}}</a></li>
        @endif
    </ul>
</div>
