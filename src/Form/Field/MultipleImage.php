<?php

namespace LaveroAdmin\Admin\Form\Field;

use LaveroAdmin\Admin\Form\Field\Traits\ImageField;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultipleImage extends MultipleFile
{
    use ImageField;

    /**
     * {@inheritdoc}
     */
    protected $view = 'admin::form.multiplefile';

    /**
     *  Validation rules.
     *
     * @var string
     */
    protected $rules = 'image';

    protected function setType($type = 'image')
    {
        $this->options['type'] = $type;
    }

    /**
     * Prepare for each file in multi-image mode.
     *
     * @param UploadedFile $image
     *
     * @return mixed|string
     */
    protected function prepareForeach(?UploadedFile $image = null)
    {
        if (is_null($image)) {
            return false;
        }

        $this->name = $this->getStoreName($image);

        $this->callInterventionMethods($image->getRealPath());

        $path = $this->upload($image);

        // Generate all configured thumbnails for this uploaded image
        $this->generateThumbnails($image, $this->name);

        $this->name = null;

        return $path;
    }

    /**
     * Force file type to image for preview.
     *
     * @param mixed $file
     *
     * @return array
     */
    public function guessPreviewType($file)
    {
        $extra         = parent::guessPreviewType($file);
        $extra['type'] = 'image';

        return $extra;
    }
}
