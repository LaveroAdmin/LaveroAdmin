<?php

namespace LaveroAdmin\Admin\Traits;

trait HasAssets
{
    /**
     * @var array
     */
    public static $script = [];

    /**
     * @var array
     */
    public static $deferredScript = [];

    /**
     * @var array
     */
    public static $style = [];

    /**
     * @var array
     */
    public static $css = [];

    /**
     * @var array
     */
    public static $js = [];

    /**
     * @var array
     */
    public static $html = [];

    /**
     * @var array
     */
    public static $headerJs = [];

    /**
     * @var string
     */
    public static $manifest = 'vendor/lavero-admin/minify-manifest.json';

    /**
     * @var array
     */
    public static $manifestData = [];

    /**
     * @var array
     */
    public static $min = [
        'js'  => 'vendor/lavero-admin/lavero-admin.min.js',
        'css' => 'vendor/lavero-admin/lavero-admin.min.css',
    ];

    /**
     * @var array
     */
    public static $baseCss = [
        // first libraries
        'vendor/lavero-admin/nprogress/nprogress.css',
        'vendor/lavero-admin/sweetalert2/sweetalert2.min.css',
        'vendor/lavero-admin/toastify-js/toastify.css',
        'vendor/lavero-admin/flatpickr/flatpicker-custom.css',
        'vendor/lavero-admin/choicesjs/styles/choices.min.css',
        'vendor/lavero-admin/sortablejs/nestable.css',

        // custom lavero admin stuff
        // generated through sass
        'vendor/lavero-admin/lavero-admin/css/styles.css',
    ];

    /**
     * @var array
     */
    public static $baseJs = [
        'vendor/lavero-admin/bootstrap5/bootstrap.bundle.min.js',
        'vendor/lavero-admin/nprogress/nprogress.js',
        'vendor/lavero-admin/axios/axios.min.js',
        'vendor/lavero-admin/sweetalert2/sweetalert2.min.js',
        'vendor/lavero-admin/toastify-js/toastify.js',
        'vendor/lavero-admin/flatpickr/flatpickr.min.js',
        'vendor/lavero-admin/choicesjs/scripts/choices.min.js',
        'vendor/lavero-admin/sortablejs/Sortable.min.js',

        'vendor/lavero-admin/lavero-admin/js/polyfills.js',
        'vendor/lavero-admin/lavero-admin/js/helpers.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin-actions.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin-grid.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin-grid-inline-edit.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin-form.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin-toastr.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin-resource.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin-tree.js',
        'vendor/lavero-admin/lavero-admin/js/lavero-admin-selectable.js',

    ];

    /**
     * @var array
     */
    public static $minifyIgnoresCss = [];
    public static $minifyIgnoresJs = [];

    /**
     * Add css or get all css.
     *
     * @param null $css
     * @param bool $minify
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function css($css = null, $minify = true)
    {
        static::ignoreMinify('css', $css, $minify);

        if (!is_null($css)) {
            return self::$css = array_merge(self::$css, (array) $css);
        }

        if (!$css = static::getMinifiedCss()) {
            $css = array_merge(static::$css, static::baseCss());
        }

        $css = array_merge($css, static::$minifyIgnoresCss); // add minified ignored files
        $css = array_filter(array_unique($css));

        return view('admin::partials.css', compact('css'));
    }

    /**
     * @param null $css
     * @param bool $minify
     *
     * @return array|null
     */
    public static function baseCss($css = null, $minify = true)
    {
        static::ignoreMinify('css', $css, $minify);

        if (!is_null($css)) {
            return static::$baseCss = $css;
        }

        $skin = config('admin.skin', 'skin-blue-light');
        //array_unshift(static::$baseCss, "vendor/lavero-admin/AdminLTE/dist/css/skins/{$skin}.min.css");

        return static::$baseCss;
    }

    /**
     * Add js or get all js.
     *
     * @param null $js
     * @param bool $minify
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function js($js = null, $minify = true)
    {
        static::ignoreMinify('js', $js, $minify);

        if (!is_null($js)) {
            return self::$js = array_merge(self::$js, (array) $js);
        }

        if (!$js = static::getMinifiedJs()) {
            $js = array_merge(static::baseJs(), static::$js);
        }

        $js = array_merge($js, static::$minifyIgnoresJs); // add minified ignored files
        $js = array_filter(array_unique($js));

        return view('admin::partials.js', compact('js'));
    }

    /**
     * Add js or get all js.
     *
     * @param null $js
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function headerJs($js = null)
    {
        if (!is_null($js)) {
            return self::$headerJs = array_merge(self::$headerJs, (array) $js);
        }

        return view('admin::partials.js', ['js' => array_unique(static::$headerJs)]);
    }

    /**
     * @param null $js
     * @param bool $minify
     *
     * @return array|null
     */
    public static function baseJs($js = null, $minify = true)
    {
        static::ignoreMinify('js', $js, $minify);

        if (!is_null($js)) {
            return static::$baseJs = $js;
        }

        $baseJs = static::$baseJs;
        $locale = config('admin.locale', config('app.locale'));
        if ($locale === 'ru') {
            $baseJs[] = 'vendor/lavero-admin/flatpickr/l10n/ru.js';
        }

        return $baseJs;
    }

    /**
     * @param string $assets
     * @param bool   $ignore
     */
    public static function ignoreMinify($type, $assets, $ignore = true)
    {
        if (!$ignore) {
            if ($type == 'css') {
                static::$minifyIgnoresCss[] = $assets;
            } else {
                static::$minifyIgnoresJs[] = $assets;
            }
        }
    }

    /**
     * @param string $script
     * @param bool   $deferred
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function script($script = '', $deferred = false)
    {
        if (!empty($script)) {
            if ($deferred) {
                return self::$deferredScript = array_merge(self::$deferredScript, (array) $script);
            }

            return self::$script = array_merge(self::$script, (array) $script);
        }

        $script = collect(static::$script)
            ->merge(static::$deferredScript)
            ->unique()
            ->map(function ($line) {
                return $line;
                //@see https://stackoverflow.com/questions/19509863/how-to-remove-js-comments-using-php
                $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
                $line = preg_replace($pattern, '', $line);

                return preg_replace('/\s+/', ' ', $line);
            });

        return view('admin::partials.script', compact('script'));
    }

    /**
     * @param string $style
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function style($style = '')
    {
        if (!empty($style)) {
            return self::$style = array_merge(self::$style, (array) $style);
        }

        $style = collect(static::$style)
            ->unique()
            ->map(function ($line) {
                return preg_replace('/\s+/', ' ', $line);
            });

        return view('admin::partials.style', compact('style'));
    }

    /**
     * @param string $html
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function html($html = '')
    {
        if (!empty($html)) {
            return self::$html = array_merge(self::$html, (array) $html);
        }

        return view('admin::partials.html', ['html' => array_unique(self::$html)]);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected static function getManifestData($key)
    {
        if (!empty(static::$manifestData)) {
            return static::$manifestData[$key];
        }

        static::$manifestData = json_decode(
            file_get_contents(public_path(static::$manifest)),
            true
        );

        return static::$manifestData[$key];
    }

    /**
     * @return bool|mixed
     */
    protected static function getMinifiedCss()
    {
        if (!config('admin.minify_assets') || !file_exists(public_path(static::$manifest))) {
            return false;
        }

        return static::getManifestData('css');
    }

    /**
     * @return bool|mixed
     */
    protected static function getMinifiedJs()
    {
        if (!config('admin.minify_assets') || !file_exists(public_path(static::$manifest))) {
            return false;
        }

        return static::getManifestData('js');
    }

    /**
     * @param $component
     */
    public static function component($component, $data = [])
    {
        $string = view($component, $data)->render();

        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$string);
        libxml_use_internal_errors(false);

        if ($head = $dom->getElementsByTagName('head')->item(0)) {
            foreach ($head->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    if ($child->tagName == 'style' && !empty($child->nodeValue)) {
                        static::style($child->nodeValue);
                        continue;
                    }

                    if ($child->tagName == 'link' && $child->hasAttribute('href')) {
                        static::css($child->getAttribute('href'));
                    }

                    if ($child->tagName == 'script') {
                        if ($child->hasAttribute('src')) {
                            static::js($child->getAttribute('src'));
                        } else {
                            static::script(';(function () {'.$child->nodeValue.'})();');
                        }

                        continue;
                    }
                }
            }
        }

        $render = '';

        if ($body = $dom->getElementsByTagName('body')->item(0)) {
            foreach ($body->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    if ($child->tagName == 'style' && !empty($child->nodeValue)) {
                        static::style($child->nodeValue);
                        continue;
                    }

                    if ($child->tagName == 'script' && !empty($child->nodeValue)) {
                        static::script(';(function () {'.$child->nodeValue.'})();');
                        continue;
                    }

                    if ($child->tagName == 'template') {
                        if ($child->getAttribute('render') == 'true') {
                            // this will render the template tags right into the dom. Don't think we want this
                            $html = '';
                            foreach ($child->childNodes as $childNode) {
                                $html .= $child->ownerDocument->saveHTML($childNode);
                            }
                        } else {
                            // this leaves the template tags in place, so they won't get rendered right away
                            $sub_doc = new \DOMDocument();
                            $sub_doc->appendChild($sub_doc->importNode($child, true));
                            $html = $sub_doc->saveHTML();
                        }
                        $html && static::html($html);

                        continue;
                    }
                }

                $render .= $body->ownerDocument->saveHTML($child);
            }
        }

        return trim($render);
    }
}
