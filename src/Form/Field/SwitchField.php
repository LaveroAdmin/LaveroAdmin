<?php

namespace LaveroAdmin\Admin\Form\Field;

use Illuminate\Support\Arr;
use LaveroAdmin\Admin\Form\Field;
use LaveroAdmin\Admin\Form\Field\Traits\CanCascadeFields;

class SwitchField extends Field
{
    use CanCascadeFields;

    /**
     * @var string
     */
    protected $cascadeEvent = 'change';
    /**
     * @var array
     */
    protected $states = [
        'on'  => ['value' => 1, 'text' => '', 'color' => 'primary'],
        'off' => ['value' => 0, 'text' => '', 'color' => 'default'],
    ];

    /**
     * Set states for switch.
     *
     * @param array $states
     * @return $this|array
     */
    public function states($states = [])
    {
        if (empty($states)) {
            return $this->states;
        }

        foreach (Arr::dot($states) as $key => $state) {
            Arr::set($this->states, $key, $state);
        }

        return $this;
    }

    public function prepare($value)
    {
        if (isset($this->states[$value])) {
            return $this->states[$value]['value'];
        }

        foreach ($this->states as $state) {
            if (isset($state['value']) && (string)$value === (string)$state['value']) {
                return $state['value'];
            }
        }

        if ($value === 'on' || $value === 1 || $value === '1' || $value === true) {
            return $this->states['on']['value'] ?? 1;
        } elseif ($value === 'off' || $value === 0 || $value === '0' || $value === false) {
            return $this->states['off']['value'] ?? 0;
        } else {
            return false; // nothing was set so do: false to ignore value from saving
        }
    }

    public function render()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        $val = old($this->column, $this->value());
        $onValue = $this->states['on']['value'] ?? 1;
        $offValue = $this->states['off']['value'] ?? 0;

        if ($val === null || $val === '') {
            $checked = false;
        } elseif ((string)$val === (string)$onValue || $val === 'on' || $val === true) {
            $checked = true;
        } elseif ((string)$val === (string)$offValue || $val === 'off' || $val === false) {
            $checked = false;
        } else {
            $checked = !empty($val);
        }

        $this->addVariables([
            'states'  => $this->states,
            'checked' => $checked,
        ]);

        $this->addCascadeScript();

        return parent::render();
    }
}

