<?php

namespace LaveroAdmin\Admin\Form\Field;

use LaveroAdmin\Admin\Form\Field;

class Display extends Field
{
    public function prepare($value)
    {
        return $this->original();
    }
}
