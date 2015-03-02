<?php 

namespace Stevebauman\CoreHelper\Services;

/**
 * Class AbstractNestedSetModelService
 * @package Stevebauman\CoreHelper\Services
 */
abstract class AbstractNestedSetModelService extends ModelService
{
    /**
     * @return mixed
     */
    public function roots()
    {
        return $this->model->roots()->where('belongs_to', $this->scoped_id)->get();
    }
}