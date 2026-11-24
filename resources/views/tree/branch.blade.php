@php $hasChildren = !empty($branch['children']); @endphp
<li class="dd-item {{ $hasChildren ? 'has-children' : '' }}" data-id="{{ $branch[$keyName] }}">
    <button type="button" data-action="collapse">{{ trans('admin.collapse') }}</button>
    <button type="button" data-action="expand">{{ trans('admin.expand') }}</button>
    <div class="dd-handle">
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