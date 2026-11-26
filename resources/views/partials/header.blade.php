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

        <ul class="nav ms-auto d-flex align-items-center header-right-nav">
            {!! Admin::getNavbar()->render() !!}

            <li class="nav-item">
                <a href="javascript:void(0);" class="nav-link px-2 px-md-3 text-secondary" onclick="if (window.admin && admin.ajax) { admin.ajax.reload(); } else { window.location.reload(); }" title="{{ trans('admin.refresh') }}" aria-label="{{ trans('admin.refresh') }}">
                    <i class="icon-sync-alt"></i>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="nav-link px-2 px-md-3 text-secondary" title="{{ config('app.name', 'Site') }}" aria-label="{{ config('app.name', 'Site') }}">
                    <i class="icon-external-link-alt"></i>
                </a>
            </li>

            <li class="nav-item">
                <div class="dropdown user-menu d-flex align-items-center px-3" href="#" role="button" id="user-menu-link" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="bg-light inline rounded-circle user-image">
                        <img src="{{ Admin::user()->avatar }}" alt="User Image">
                    </span>
                    <span class="hidden-xs">{{ Admin::user()->name }}</span>
                </div>
                <ul class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="user-menu-link">
                    <!-- The user image in the menu -->
                    <li class="user-header text-center bg-semi-dark p-3">
                        <span class="bg-light inline rounded-circle user-image medium">
                            <img src="{{ Admin::user()->avatar }}" alt="User Image">
                        </span>
                        <p>
                            <h2>{{ Admin::user()->name }}</h2>
                            <small>Member since admin {{ Admin::user()->created_at }}</small>
                        </p>
                    </li>
                    <li class="user-footer p-2 clearfix">
                        <div class="float-start">
                            <a href="{{ admin_url('auth/setting') }}" class="btn btn-secondary">{{ __('admin.setting') }}</a>
                        </div>
                        <div class="float-end">
                            <a href="{{ admin_url('auth/logout') }}" class="btn no-ajax btn-secondary">{{ __('admin.logout') }}</a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>