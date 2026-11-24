<div class="dropdown btn-group grid-column-selector me-1" id="grid-column-selector" data-defaults='{{ implode(",",$defaults) }}'>
    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="{{ __('admin.columns') ?? 'Columns' }}">
        <i class="icon-table"></i>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 220px; max-height: 420px; overflow-y: auto;">
        <li class="dropdown-header text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">{{ __('admin.columns') ?? 'Columns' }}</li>
        @foreach($columns as $key => $label)
        @php
        if (empty($visible)) {
            $checked = 'checked';
        } else {
            $checked = in_array($key, $visible) ? 'checked' : '';
        }
        @endphp

        <li>
            <label class="dropdown-item d-flex align-items-center py-1 px-3 cursor-pointer user-select-none" for="column-select-{{ $key }}">
                <input type="checkbox" class="form-check-input column-selector me-2 mt-0" id="column-select-{{ $key }}" value="{{ $key }}" {{ $checked }}/>
                <span>{{ $label }}</span>
            </label>
        </li>
        @endforeach

        <li><hr class="dropdown-divider my-2"></li>
        <li class="d-flex justify-content-between px-3 py-1 gap-2">
            <button type="button" class="btn btn-xs btn-outline-secondary column-select-all" onclick="admin.grid.columns.all()">{{ __('admin.all') }}</button>
            <button type="button" class="btn btn-xs btn-outline-secondary column-select-reset" onclick="admin.grid.columns.reset()">{{ __('admin.reset') }}</button>
            <button type="button" class="btn btn-xs btn-primary column-select-submit" onclick="admin.grid.columns.submit()">{{ __('admin.submit') }}</button>
        </li>
    </ul>
</div>