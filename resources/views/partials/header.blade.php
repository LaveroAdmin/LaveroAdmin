<!-- Main Header -->
<header class="custom-navbar navbar navbar-light bg-white p-0 align-items-stretch">
    <!-- Menu toggle button -->
    <a class="flex-shrink valign-header link-secondary menu-toggle-btn" type="button" id='menu-toggle' aria-controls="menu" aria-expanded="false" aria-label="{{ trans('admin.toggle_navigation') }}">
        <i class="icon-bars"></i>
    </a>

    <!-- Brand / Logo -->
    <a class="navbar-brand menu-width bg-semi-dark text-center" href="{{ admin_url('/') }}">
        <span class="short">{!! config('admin.logo-mini', config('admin.name')) !!}</span><span class="long">{!! config('admin.logo', config('admin.name')) !!}</span>
    </a>

    <!-- Header Items -->
    <div class="d-flex flex-fill header-items align-items-stretch">
        <ul class="nav navbar-nav hidden-sm visible-lg-block">
            {!! Admin::getNavbar()->render('left') !!}
        </ul>

        <div class="flex-fill search" style="display:none;">
            <input class="form-control" type="text" placeholder="{{ trans('admin.search') }}" aria-label="{{ trans('admin.search') }}">
        </div>

        <ul class="nav ms-auto d-flex align-items-center gap-2 pe-2 pe-md-3 header-right-nav">
            {!! Admin::getNavbar()->render() !!}

            <li class="nav-item">
                <a href="javascript:void(0);" class="header-action-btn" onclick="if (window.admin && admin.ajax) { admin.ajax.reload(); } else { window.location.reload(); }" title="{{ trans('admin.refresh') }}" aria-label="{{ trans('admin.refresh') }}">
                    <i class="icon-sync-alt"></i>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="header-action-btn" title="{{ config('app.name', 'Site') }}" aria-label="{{ config('app.name', 'Site') }}">
                    <i class="icon-external-link-alt"></i>
                </a>
            </li>

            <li class="nav-item dropdown">
                <div class="user-menu user-menu-trigger d-flex align-items-center" href="#" role="button" id="user-menu-link" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="bg-light inline rounded-circle user-image">
                        <img src="{{ Admin::user()->avatar }}" alt="User Image">
                    </span>
                    <span class="hidden-xs fw-semibold text-dark small">{{ Admin::user()->name }}</span>
                    <i class="icon-chevron-down hidden-xs text-muted" style="font-size: 0.7rem;"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="user-menu-link">
                    <li class="user-header text-center p-3">
                        <span class="bg-light inline rounded-circle user-image medium mb-2">
                            <img src="{{ Admin::user()->avatar }}" alt="User Image">
                        </span>
                        <div class="fw-bold text-white fs-6">{{ Admin::user()->name }}</div>
                        <small class="d-block mt-1" style="color: #94a3b8; font-size: 0.75rem;">{{ Admin::user()->created_at }}</small>
                    </li>
                    <li class="user-footer p-2 d-flex align-items-center justify-content-between gap-2 bg-white">
                        <a href="{{ admin_url('auth/setting') }}" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="icon-cog me-1"></i>{{ __('admin.setting') }}
                        </a>
                        <a href="{{ admin_url('auth/logout') }}" class="btn btn-sm btn-primary flex-fill no-ajax">
                            <i class="icon-sign-out-alt me-1"></i>{{ __('admin.logout') }}
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>