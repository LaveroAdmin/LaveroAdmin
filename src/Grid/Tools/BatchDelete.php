<?php

namespace LaveroAdmin\Admin\Grid\Tools;

use LaveroAdmin\Admin\Actions\BatchAction;

class BatchDelete extends BatchAction
{
    public $icon = 'icon-trash';

    public function __construct()
    {
        $this->name = trans('admin.batch_delete');
    }

    /**
     * Script is handled via script() in BatchActions::addActionScripts().
     */
    protected function addScript()
    {
        // Prevent registering admin.actions.add since BatchDelete uses admin.resource.batch_delete
    }

    /**
     * Script of batch delete action.
     */
    public function script()
    {
        return <<<JS
        document.querySelector('{$this->getSelector()}').addEventListener("click",function(){
            let resource_url = '{$this->resource}/' + admin.grid.selected.join();
            admin.resource.batch_delete(resource_url);
        });
JS;
    }
}
