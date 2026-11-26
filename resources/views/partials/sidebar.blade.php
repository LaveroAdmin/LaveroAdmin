<aside id="sidebar" class="menu-width sidebar bg-semi-dark">

    @if(config('admin.enable_user_panel'))
        <div class="user-panel d-flex align-items-center gap-2 px-3 py-3" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <div class="flex-shrink-0">
                <img src="{{ Admin::user()->avatar }}" class="rounded-circle" alt="User Image" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #bef264;">
            </div>
            <div class="user-panel-info overflow-hidden">
                <div class="fw-semibold text-white text-truncate small">{{ Admin::user()->name }}</div>
                <span class="small d-inline-flex align-items-center gap-1" style="color: #94a3b8; font-size: 0.75rem;">
                    <span class="rounded-circle d-inline-block" style="width: 7px; height: 7px; background: #10b981;"></span>
                    {{ trans('admin.online') }}
                </span>
            </div>
        </div>
    @endif

    @if(config('admin.enable_menu_search'))
        <!-- search form (Optional) -->
        <form class="sidebar-form" style="overflow: initial;" onsubmit="return false;">
            <div class="input-group">
                <input type="text" autocomplete="off" class="form-control autocomplete" placeholder="{{ trans('admin.search') }}...">
                <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="icon-search"></i></button>
            </span>
                <ul class="dropdown-menu" role="menu" style="min-width: 210px;max-height: 300px;overflow: auto;">
                    @foreach(Admin::menuLinks() as $link)
                        <li>
                            <a href="{{ admin_url($link['uri']) }}"><i class="{{ $link['icon'] }}"></i>{{ admin_trans($link['title']) }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </form>
        <!-- /.search form -->
    @endif

    <nav>
        <div class="custom-menu">
            <ul class="list-unstyled ps-0 root" id="menu">
                @each('admin::partials.menu', Admin::menu(), 'item')
            </ul>
        </div>
    </nav>

    <div class="sidebar-copyright">
        <a href="https://bespredel.name" target="_blank" rel="noopener" title="Aleksandr Kireev">
            &copy; {{ date('Y') }} <span class="sidebar-copyright-text">Aleksandr Kireev</span>
        </a>
    </div>
</aside>