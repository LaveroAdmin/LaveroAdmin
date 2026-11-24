<?php

namespace LaveroAdmin\Admin\Form\Field\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ImageField
{
    /**
     * Intervention calls.
     *
     * @var array
     */
    protected $interventionCalls = [];

    /**
     * Thumbnail settings.
     *
     * @var array
     */
    protected $thumbnails = [];

    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('admin.upload.directory.image');
    }

    /**
     * Get Intervention ImageManager instance for intervention/image v4.
     *
     * @return \Intervention\Image\ImageManager
     * @throws \Exception
     */
    protected function getImageManager()
    {
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $driverClass = config('admin.upload.image_driver');

            if (!$driverClass) {
                if (extension_loaded('gd') && class_exists(\Intervention\Image\Drivers\Gd\Driver::class)) {
                    $driverClass = \Intervention\Image\Drivers\Gd\Driver::class;
                } elseif (extension_loaded('imagick') && class_exists(\Intervention\Image\Drivers\Imagick\Driver::class)) {
                    $driverClass = \Intervention\Image\Drivers\Imagick\Driver::class;
                } elseif (class_exists(\Intervention\Image\Drivers\Gd\Driver::class)) {
                    $driverClass = \Intervention\Image\Drivers\Gd\Driver::class;
                }
            }

            if ($driverClass) {
                if (method_exists(\Intervention\Image\ImageManager::class, 'usingDriver')) {
                    return \Intervention\Image\ImageManager::usingDriver($driverClass);
                }

                return new \Intervention\Image\ImageManager(new $driverClass());
            }

            if (class_exists(\Intervention\Image\Drivers\Gd\Driver::class)) {
                return new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            }
        }

        if (class_exists('Intervention\Image\Laravel\Facades\Image')) {
            return forward_static_call([\Intervention\Image\Laravel\Facades\Image::class, 'getFacadeRoot']);
        }

        throw new \Exception('To use image handling and manipulation, please install [intervention/image: ^4.0].');
    }

    /**
     * Decode image from path or file using Intervention Image v4.
     *
     * @param mixed $source
     * @return \Intervention\Image\Interfaces\ImageInterface
     * @throws \Exception
     */
    protected function decodeImage($source)
    {
        $manager = $this->getImageManager();

        return $manager->decode($source);
    }

    /**
     * Resolve flip direction argument for Intervention Image v4 Direction enum.
     *
     * @param mixed $direction
     * @return mixed
     */
    protected function resolveFlipDirection($direction)
    {
        if (class_exists(\Intervention\Image\Direction::class)) {
            if (in_array(strtolower((string) $direction), ['v', 'vertical', 'y'])) {
                return \Intervention\Image\Direction::VERTICAL;
            }

            return \Intervention\Image\Direction::HORIZONTAL;
        }

        return $direction;
    }

    /**
     * Execute Intervention calls on image file.
     *
     * @param string $target
     *
     * @return mixed
     */
    public function callInterventionMethods($target)
    {
        if (!empty($this->interventionCalls)) {
            $image = $this->decodeImage($target);

            foreach ($this->interventionCalls as $call) {
                $method = $call['method'];
                $arguments = $call['arguments'];

                if ($method === 'flip' && !empty($arguments) && is_string($arguments[0])) {
                    $arguments[0] = $this->resolveFlipDirection($arguments[0]);
                }

                $result = call_user_func_array([$image, $method], $arguments);

                if (is_object($result) && method_exists($result, 'save')) {
                    $image = $result;
                }
            }

            $image->save($target);
        }

        return $target;
    }

    /**
     * Call intervention methods fluently.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        if (static::hasMacro($method)) {
            return $this;
        }

        if (!class_exists(\Intervention\Image\ImageManager::class)
            && !class_exists('Intervention\Image\Laravel\Facades\Image')
        ) {
            throw new \Exception('To use image handling and manipulation, please install [intervention/image: ^4.0] first.');
        }

        $this->interventionCalls[] = [
            'method'    => $method,
            'arguments' => $arguments,
        ];

        return $this;
    }

    /**
     * Render a image form field.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->options(['allowedFileTypes' => ['image'], 'msgPlaceholder' => trans('admin.choose_image')]);

        return parent::render();
    }

    /**
     * Set thumbnail configuration.
     *
     * @param string|array $name
     * @param int|null     $width
     * @param int|null     $height
     * @param string|null  $action
     * @param string|null  $background
     *
     * @return $this
     */
    public function thumbnail($name, $width = null, $height = null, $action = null, $background = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $size) {
                if ($size instanceof \Closure) {
                    $this->thumbnails[$key] = $size;
                } elseif (is_array($size) && count($size) >= 2) {
                    $this->thumbnails[$key] = $size;
                }
            }
        } else {
            $size = [$width, $height];
            if (!is_null($action)) {
                $size[] = $action;
            }
            if (!is_null($background)) {
                $size[] = $background;
            }
            $this->thumbnails[$name] = $size;
        }

        return $this;
    }

    /**
     * Set a custom thumbnail closure function.
     *
     * @param string   $name
     * @param \Closure $function
     *
     * @return $this
     */
    public function thumbnailFunction($name, \Closure $function)
    {
        $this->thumbnails[$name] = $function;

        return $this;
    }

    /**
     * Apply thumbnail size transformations in Intervention Image v4.
     *
     * @param mixed $image
     * @param array $size
     * @return mixed
     */
    protected function applyThumbnailSize($image, array $size)
    {
        $width = $size[0] ?? null;
        $height = $size[1] ?? null;
        $action = strtolower($size[2] ?? 'contain');
        $background = $size[3] ?? '#ffffff';

        switch ($action) {
            case 'cover':
            case 'fit':
                return $image->cover($width, $height);
            case 'coverdown':
            case 'fitdown':
                return $image->coverDown($width, $height);
            case 'scale':
                return $image->scale($width, $height);
            case 'scaledown':
                return $image->scaleDown($width, $height);
            case 'stretch':
                return $image->resize($width, $height);
            case 'containdown':
            case 'paddown':
                return $image->containDown($width, $height, $background);
            case 'contain':
            case 'pad':
            case 'resize':
            default:
                return $image->contain($width, $height, $background);
        }
    }

    /**
     * Generate all configured thumbnails for a given file.
     *
     * @param UploadedFile $file
     * @param string|null  $storeName
     * @return void
     */
    public function generateThumbnails(UploadedFile $file, $storeName = null)
    {
        $storeName = $storeName ?: $this->name;

        if (empty($this->thumbnails) || empty($storeName)) {
            return;
        }

        foreach ($this->thumbnails as $name => $size_or_closure) {
            $ext = pathinfo($storeName, PATHINFO_EXTENSION);
            $baseName = Str::replaceLast('.'.$ext, '', $storeName);
            $targetPath = $baseName.'-'.$name.'.'.$ext;

            $image = $this->decodeImage($file->getRealPath());

            if ($size_or_closure instanceof \Closure) {
                $image = $size_or_closure->call($this, $image);
            } elseif (is_array($size_or_closure)) {
                $image = $this->applyThumbnailSize($image, $size_or_closure);
            }

            $encodedContent = (string) $image->encode();

            $fullPath = "{$this->getDirectory()}/{$targetPath}";

            if (!is_null($this->storagePermission)) {
                $this->storage->put($fullPath, $encodedContent, $this->storagePermission);
            } else {
                $this->storage->put($fullPath, $encodedContent);
            }
        }
    }

    /**
     * Upload file and delete original thumbnail files (used by single Image).
     *
     * @param UploadedFile $file
     *
     * @return $this
     */
    protected function uploadAndDeleteOriginalThumbnail(UploadedFile $file)
    {
        $this->generateThumbnails($file, $this->name);

        $this->destroyThumbnail();

        return $this;
    }

    /**
     * Destroy original thumbnail files.
     *
     * @param bool $delete_all
     * @return void
     */
    public function destroyThumbnail($delete_all = false)
    {
        if ($this->retainable) {
            return;
        }

        foreach ($this->thumbnails as $name => $_) {
            if (is_array($this->original)) {
                if (empty($this->original)) {
                    continue;
                }
                if ($delete_all) {
                    foreach ($this->original as $original) {
                        if (is_array($original) && isset($this->pathColumn)) {
                            $original = Arr::get($original, $this->pathColumn);
                        }
                        if (is_string($original)) {
                            $this->destroyThumbnailFile($original, $name);
                        }
                    }
                }
            } elseif (is_string($this->original)) {
                $this->destroyThumbnailFile($this->original, $name);
            }
        }
    }

    /**
     * Remove thumbnail file from disk.
     *
     * @param string $original
     * @param string $name
     * @return void
     */
    public function destroyThumbnailFile($original, $name)
    {
        if (empty($original) || !is_string($original)) {
            return;
        }

        $ext = pathinfo($original, PATHINFO_EXTENSION);

        // We remove extension from file name so we can append thumbnail type
        $path = Str::replaceLast('.'.$ext, '', $original);
        $thumbnailPath = $path.'-'.$name.'.'.$ext;

        if ($this->storage->exists($thumbnailPath)) {
            $this->storage->delete($thumbnailPath);
            return;
        }

        $dirPath = "{$this->getDirectory()}/{$thumbnailPath}";
        if ($this->storage->exists($dirPath)) {
            $this->storage->delete($dirPath);
        }
    }
}
