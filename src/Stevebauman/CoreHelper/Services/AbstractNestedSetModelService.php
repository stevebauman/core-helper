<?php 

namespace Stevebauman\CoreHelper\Services;

use Stevebauman\CoreHelper\Services\AbstractModelService;

abstract class AbstractNestedSetModelService extends AbstractModelService {

    public function roots()
    {
        return $this->model->roots()->where('belongs_to', $this->scoped_id)->get();
    }
    
}