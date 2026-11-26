
<div class="card p-0">
    @if(isset($title))
        <div class="card-header">
            <h3 class="card-title"> {{ $title }}</h3>
        </div>
    @endif

	<div class="container-fluid card-header no-border grid-toolbar-header">
        @if ( $grid->showTools() || $grid->showExportBtn() || $grid->showCreateBtn() )
        <div class="row g-2 align-items-center grid-toolbar-row">
            <div class="col-12 col-md-auto me-md-auto d-flex flex-wrap align-items-center gap-2 grid-toolbar-left">
                {!! $grid->renderCreateButton() !!}
                @if ( $grid->showTools() )
                {!! $grid->renderHeaderTools() !!}
                @endif
            </div>
            <div class="col-12 col-md-auto d-flex flex-wrap align-items-center justify-content-start justify-content-md-end gap-2 grid-toolbar-right">
                {!! $grid->renderExportButton() !!}
                {!! $grid->renderColumnSelector() !!}
            </div>
        </div>
        @endif
    </div>
    {!! $grid->renderFilter() !!}
    {!! $grid->renderHeader() !!}