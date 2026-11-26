<?php

namespace LaveroAdmin\Admin;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use LaveroAdmin\Admin\Auth\Database\Menu;
use LaveroAdmin\Admin\Controllers\AuthController;
use LaveroAdmin\Admin\Layout\Content;
use LaveroAdmin\Admin\Traits\HasAssets;
use LaveroAdmin\Admin\Widgets\Navbar;

/**
 * Class Admin.
 */
class Admin
{
    use HasAssets;

    /**
     * The Lavero-admin version.
     *
     * @var string
     */
    public const VERSION = '1.0.38';

    /**
     * @var Navbar
     */
    protected $navbar;

    /**
     * @var array
     */
    protected $menu = [];

    /**
     * @var string
     */
    public static $metaTitle;

    /**
     * @var string
     */
    public static $favicon;

    /**
     * @var array
     */
    public static $extensions = [];

    /**
     * @var []Closure
     */
    protected static $bootingCallbacks = [];

    /**
     * @var []Closure
     */
    protected static $bootedCallbacks = [];

    /**
     * Returns the version of Lavero-admin.
     *
     * @return string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Returns the long version of Lavero-admin.
     *
     * @return string The long application version
     */
    public static function getLongVersion()
    {
        return sprintf('Lavero Admin <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \LaveroAdmin\Admin\Grid
     *
     * @deprecated since v1.6.1
     */
    public function grid($model, Closure $callable)
    {
        return new Grid($this->getModel($model), $callable);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \LaveroAdmin\Admin\Form
     *
     *  @deprecated since v1.6.1
     */
    public function form($model, Closure $callable)
    {
        return new Form($this->getModel($model), $callable);
    }

    /**
     * Build a tree.
     *
     * @param $model
     * @param Closure|null $callable
     *
     * @return \LaveroAdmin\Admin\Tree
     */
    public function tree($model, ?Closure $callable = null)
    {
        return new Tree($this->getModel($model), $callable);
    }

    /**
     * Build show page.
     *
     * @param $model
     * @param mixed $callable
     *
     * @return Show
     */
    public function show($model, $callable = null)
    {
        return new Show($this->getModel($model), $callable);
    }

    /**
     * @param Closure $callable
     *
     * @return \LaveroAdmin\Admin\Layout\Content
     */
    public function content(?Closure $callable = null)
    {
        return new Content($callable);
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return $this->getModel(new $model());
        }

        throw new InvalidArgumentException("$model is not a valid model");
    }

    /**
     * Left sider-bar menu.
     *
     * @return array
     */
    public function menu()
    {
        if (!empty($this->menu)) {
            return $this->menu;
        }

        $menuClass = config('admin.database.menu_model');

        /** @var Menu $menuModel */
        $menuModel = new $menuClass();

        $menu = $menuModel->toTree();

        return $this->menu = $this->filterDisabledExtensions($menu);
    }

    /**
     * Filter out menu items belonging to disabled extensions.
     *
     * @param array $menu
     * @return array
     */
    protected function filterDisabledExtensions(array $menu): array
    {
        $disabledUris = [];

        $extensionsConfig = config('admin.extensions', []);
        if (is_array($extensionsConfig)) {
            foreach ($extensionsConfig as $name => $cfg) {
                if (is_array($cfg) && isset($cfg['enable']) && $cfg['enable'] === false) {
                    $disabledUris[] = strtolower($name);
                }
            }
        }

        foreach (static::$extensions as $name => $class) {
            if (class_exists($class) && method_exists($class, 'disabled') && $class::disabled()) {
                $disabledUris[] = strtolower($name);
            }
        }

        $uriAliases = [
            'log-viewer'    => ['logs'],
            'media-manager' => ['media'],
            'reporter'      => ['exceptions'],
            'helpers'       => ['helpers', 'helpers/scaffold', 'helpers/terminal/database'],
        ];

        $allDisabled = [];
        foreach ($disabledUris as $u) {
            $allDisabled[] = $u;
            if (isset($uriAliases[$u])) {
                foreach ($uriAliases[$u] as $alias) {
                    $allDisabled[] = $alias;
                }
            }
        }
        $allDisabled = array_unique($allDisabled);

        if (empty($allDisabled)) {
            return $menu;
        }

        return $this->filterMenuNodes($menu, $allDisabled);
    }

    /**
     * Recursively filter menu nodes by disabled URIs.
     *
     * @param array $nodes
     * @param array $disabledUris
     * @return array
     */
    protected function filterMenuNodes(array $nodes, array $disabledUris): array
    {
        $filtered = [];

        foreach ($nodes as $node) {
            $uri = trim($node['uri'] ?? '', '/');

            $isDisabled = false;
            foreach ($disabledUris as $dUri) {
                if ($uri === $dUri || \Illuminate\Support\Str::startsWith($uri, $dUri . '/')) {
                    $isDisabled = true;
                    break;
                }
            }

            if ($isDisabled) {
                continue;
            }

            if (!empty($node['children'])) {
                $node['children'] = $this->filterMenuNodes($node['children'], $disabledUris);
                if (empty($uri) && empty($node['children'])) {
                    continue;
                }
            }

            $filtered[] = $node;
        }

        return $filtered;
    }

    /**
     * @param array $menu
     *
     * @return array
     */
    public function menuLinks($menu = [])
    {
        if (empty($menu)) {
            $menu = $this->menu();
        }

        $links = [];

        foreach ($menu as $item) {
            if (!empty($item['children'])) {
                $links = array_merge($links, $this->menuLinks($item['children']));
            } else {
                $links[] = Arr::only($item, ['title', 'uri', 'icon']);
            }
        }

        return $links;
    }

    /**
     * Set admin title.
     *
     * @param string $title
     *
     * @return void
     */
    public static function setTitle($title)
    {
        self::$metaTitle = $title;
    }

    /**
     * Get admin title.
     *
     * @return string
     */
    public function title()
    {
        return self::$metaTitle ? self::$metaTitle : config('admin.title');
    }

    /**
     * @param null|string $favicon
     *
     * @return string|void
     */
    public function favicon($favicon = null)
    {
        if (is_null($favicon)) {
            return static::$favicon;
        }

        static::$favicon = $favicon;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return static::guard()->user();
    }

    /**
     * Get the guard name.
     *
     * @return string
     */
    public function guardName()
    {
        return config('admin.auth.guard') ?: 'admin';
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard()
    {
        return Auth::guard(static::guardName());
    }

    /**
     * Set navbar.
     *
     * @param Closure|null $builder
     *
     * @return Navbar
     */
    public function navbar(?Closure $builder = null)
    {
        if (is_null($builder)) {
            return $this->getNavbar();
        }

        call_user_func($builder, $this->getNavbar());
    }

    /**
     * Get navbar object.
     *
     * @return \LaveroAdmin\Admin\Widgets\Navbar
     */
    public function getNavbar()
    {
        if (is_null($this->navbar)) {
            $this->navbar = new Navbar();
        }

        return $this->navbar;
    }

    /**
     * Register the lavero-admin builtin routes.
     *
     * @return void
     *
     * @deprecated Use Admin::routes() instead();
     */
    public function registerAuthRoutes()
    {
        $this->routes();
    }

    /**
     * Register the lavero-admin builtin routes.
     *
     * @return void
     */
    public function routes()
    {
        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        app('router')->group($attributes, function ($router) {
            /* @var \Illuminate\Support\Facades\Route $router */
            $router->namespace('\LaveroAdmin\Admin\Controllers')->group(function ($router) {
                /* @var \Illuminate\Routing\Router $router */
                $router->resource('auth/users', 'UserController')->names('admin.auth.users');
                $router->resource('auth/roles', 'RoleController')->names('admin.auth.roles');
                $router->resource('auth/permissions', 'PermissionController')->names('admin.auth.permissions');
                $router->resource('auth/menu', 'MenuController', ['except' => ['create']])->names('admin.auth.menu');
                $router->resource('auth/logs', 'LogController', ['only' => ['index', 'destroy']])->names('admin.auth.logs');

                $router->post('_handle_form_', 'HandleController@handleForm')->name('admin.handle-form');
                $router->post('_handle_action_', 'HandleController@handleAction')->name('admin.handle-action');
                $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('admin.handle-selectable');
                $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('admin.handle-renderable');
            });

            $authController = config('admin.auth.controller', AuthController::class);

            /* @var \Illuminate\Routing\Router $router */
            $router->get('auth/login', $authController.'@getLogin')->name('admin.login');
            $router->post('auth/login', $authController.'@postLogin');
            $router->get('auth/logout', $authController.'@getLogout')->name('admin.logout');
            $router->get('auth/setting', $authController.'@getSetting')->name('admin.setting');
            $router->put('auth/setting', $authController.'@putSetting');

            $router->get('auth/2fa', $authController.'@get2FaChallenge')->name('admin.2fa.challenge');
            $router->post('auth/2fa', $authController.'@post2FaChallenge');
            $router->post('auth/2fa/setup', $authController.'@setup2Fa')->name('admin.2fa.setup');
            $router->post('auth/2fa/confirm', $authController.'@confirm2Fa')->name('admin.2fa.confirm');
            $router->post('auth/2fa/disable', $authController.'@disable2Fa')->name('admin.2fa.disable');
        });
    }

    /**
     * Extend a extension.
     *
     * @param string $name
     * @param string $class
     *
     * @return void
     */
    public static function extend($name, $class)
    {
        static::$extensions[$name] = $class;
    }

    /**
     * @param callable $callback
     */
    public static function booting(callable $callback)
    {
        static::$bootingCallbacks[] = $callback;
    }

    /**
     * @param callable $callback
     */
    public static function booted(callable $callback)
    {
        static::$bootedCallbacks[] = $callback;
    }

    /**
     * Bootstrap the admin application.
     */
    public function bootstrap()
    {
        $this->fireBootingCallbacks();

        require config('admin.bootstrap', admin_path('bootstrap.php'));

        $this->addAdminAssets();

        $this->fireBootedCallbacks();
    }

    /**
     * Add JS & CSS assets to pages.
     */
    protected function addAdminAssets()
    {
        $assets = Form::collectFieldAssets();

        self::css($assets['css']);
        self::js($assets['js']);
    }

    /**
     * Call the booting callbacks for the admin application.
     */
    protected function fireBootingCallbacks()
    {
        foreach (static::$bootingCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    /**
     * Call the booted callbacks for the admin application.
     */
    protected function fireBootedCallbacks()
    {
        foreach (static::$bootedCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    public static function asset($asset)
    {
        return url('/vendor/lavero-admin/'.$asset);
    }

    public static function js_trans()
    {
        $lang_array = json_encode(__('admin'));

        return '<script>var admin_lang_arr = '.$lang_array.'</script>';
    }
}
