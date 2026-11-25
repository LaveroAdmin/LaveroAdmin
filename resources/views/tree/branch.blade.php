@php $hasChildren = !empty($branch['children']); @endphp
<li class="dd-item {{ $hasChildren ? 'has-children' : '' }}" data-id="{{ $branch[$keyName] }}">
    <div class="dd-handle">
        <span class="dd-grip" aria-hidden="true">
            <svg width="12" height="16" viewBox="0 0 12 16" fill="currentColor">
                <circle cx="3.5" cy="3" r="1.25"/><circle cx="8.5" cy="3" r="1.25"/>
                <circle cx="3.5" cy="8" r="1.25"/><circle cx="8.5" cy="8" r="1.25"/>
                <circle cx="3.5" cy="13" r="1.25"/><circle cx="8.5" cy="13" r="1.25"/>
            </svg>
        </span>
        <button type="button" class="dd-toggle dd-nodrag" data-action="collapse" title="{{ trans('admin.collapse') }}" aria-label="{{ trans('admin.collapse') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
        <button type="button" class="dd-toggle dd-nodrag" data-action="expand" title="{{ trans('admin.expand') }}" aria-label="{{ trans('admin.expand') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
        <div class="dd-content">
            {!! $branchCallback($branch) !!}
        </div>
        <div class="dd-actions dd-nodrag">
            <a href="{{ url("$path/$branch[$keyName]/edit") }}" class="dd-action-btn" title="{{ trans('admin.edit') }}"><i class="icon-edit"></i></a>
            <a onclick="admin.tree.delete({{ $branch[$keyName] }})" data-id="{{ $branch[$keyName] }}" class="dd-action-btn tree_branch_delete" title="{{ trans('admin.delete') }}"><i class="icon-trash"></i></a>
        </div>
    </div>
    <ol class="dd-list">
        @if($hasChildren)
            @foreach($branch['children'] as $child)
                @include($branchView, ['branch' => $child])
            @endforeach
        @endif
    </ol>
</li>