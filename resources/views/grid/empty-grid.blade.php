<tr>
    <td colspan="{{ $grid->visibleColumns()->count() }}" class="text-center py-5">
        <div class="empty-state d-flex flex-column align-items-center justify-content-center text-muted py-4">
            <div class="empty-state-icon mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 56px; height: 56px; background-color: #f1f5f9; border: 1px dashed #cbd5e1;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 8v13H3V8"></path>
                    <path d="M1 3h22v5H1z"></path>
                    <path d="M10 12h4"></path>
                </svg>
            </div>
            <h5 class="empty-state-title fw-semibold text-dark mb-1" style="font-size: 0.98rem;">{{ admin_trans('admin.empty_grid') }}</h5>
            <p class="empty-state-description small text-secondary mb-3" style="max-width: 420px;">{{ admin_trans('admin.empty_grid_description') }}</p>
            @if(request()->has('_search_') || count(request()->except(['_pjax', '_pjax_'])))
                <a href="{{ $grid->getFilter()->urlWithoutFilters() }}" class="btn btn-sm btn-outline-secondary">
                    <i class="icon-refresh"></i> {{ admin_trans('admin.reset') }}
                </a>
            @endif
        </div>
    </td>
</tr>
