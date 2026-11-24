<?php

namespace LaveroAdmin\Admin\Controllers;

use Illuminate\Support\Arr;
use LaveroAdmin\Admin\Admin;

class Dashboard
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function title()
    {
        return view('admin::dashboard.title');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function environment()
    {
        $envs = [
            ['name' => 'PHP version',       'value' => 'PHP/'.PHP_VERSION],
            ['name' => 'Laravel version',   'value' => app()->version()],
            ['name' => 'CGI',               'value' => php_sapi_name()],
            ['name' => 'Uname',             'value' => php_uname()],
            ['name' => 'Server',            'value' => Arr::get($_SERVER, 'SERVER_SOFTWARE')],

            ['name' => 'Cache driver',      'value' => config('cache.default')],
            ['name' => 'Session driver',    'value' => config('session.driver')],
            ['name' => 'Queue driver',      'value' => config('queue.default')],

            ['name' => 'Timezone',          'value' => config('app.timezone')],
            ['name' => 'Locale',            'value' => config('app.locale')],
            ['name' => 'Env',               'value' => config('app.env')],
            ['name' => 'URL',               'value' => config('app.url')],
        ];

        return view('admin::dashboard.environment', compact('envs'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function extensions()
    {
        $extensions = [
            'helpers' => [
                'name' => 'laveroadmin/helpers',
                'link' => 'https://github.com/LaveroAdmin/helpers',
                'icon' => 'cogs',
            ],
            'log-viewer' => [
                'name' => 'laveroadmin/log-viewer',
                'link' => 'https://github.com/LaveroAdmin/log-viewer',
                'icon' => 'database',
            ],
            'backup' => [
                'name' => 'laveroadmin/backup',
                'link' => 'https://github.com/LaveroAdmin/backup',
                'icon' => 'copy',
            ],
            'marketplace' => [
                'name' => 'laveroadmin/marketplace',
                'link' => 'https://github.com/LaveroAdmin/marketplace',
                'icon' => 'store',
            ],
            'queue-manager' => [
                'name' => 'laveroadmin/queue-manager',
                'link' => 'https://github.com/LaveroAdmin/queue-manager',
                'icon' => 'tasks',
            ],
            'activity-log' => [
                'name' => 'laveroadmin/activity-log',
                'link' => 'https://github.com/LaveroAdmin/activity-log',
                'icon' => 'history',
            ],
            'translations' => [
                'name' => 'laveroadmin/translations',
                'link' => 'https://github.com/LaveroAdmin/translations',
                'icon' => 'language',
            ],
            'config' => [
                'name' => 'laveroadmin/config',
                'link' => 'https://github.com/LaveroAdmin/config',
                'icon' => 'toggle-on',
            ],
            'ckeditor' => [
                'name' => 'laveroadmin/ckeditor',
                'link' => 'https://github.com/LaveroAdmin/ckeditor',
                'icon' => 'pencil-alt',
            ],
            'media-manager' => [
                'name' => 'laveroadmin/media-manager',
                'link' => 'https://github.com/LaveroAdmin/media-manager',
                'icon' => 'file',
            ],
            'scheduling' => [
                'name' => 'laveroadmin/scheduling',
                'link' => 'https://github.com/LaveroAdmin/scheduling',
                'icon' => 'clock',
            ],
            'reporter' => [
                'name' => 'laveroadmin/reporter',
                'link' => 'https://github.com/LaveroAdmin/reporter',
                'icon' => 'bug',
            ],
            'wafu' => [
                'name' => 'laveroadmin/wafu',
                'link' => 'https://github.com/LaveroAdmin/wafu',
                'icon' => 'shield-alt',
            ],
            'grid-sortable' => [
                'name' => 'laveroadmin/grid-sortable',
                'link' => 'https://github.com/LaveroAdmin/grid-sortable',
                'icon' => 'arrows-alt-v',
            ],
        ];

        foreach ($extensions as $key => &$extension) {
            $name = explode('/', $extension['name']);
            $extension['installed'] = array_key_exists($key, Admin::$extensions) || array_key_exists(end($name), Admin::$extensions);
        }

        return view('admin::dashboard.extensions', compact('extensions'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function dependencies()
    {
        $json = file_get_contents(base_path('composer.json'));

        $dependencies = json_decode($json, true)['require'];

        return Admin::component('admin::dashboard.dependencies', compact('dependencies'));
    }
}
