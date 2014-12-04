<?php 

namespace Stevebauman\CoreHelper\Services;

use Exception;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\DB;
use Stevebauman\CoreHelper\Services\AbstractService;

abstract class AbstractModelService extends AbstractService {
    
    /*
     * Holds the eloquent model to query
     */
    protected $model;
    
    /*
     * Holds the exception to be thrown when a record isn't found
     */
    protected $notFoundException;
    
    /**
     * Return all model records
     *
     * @author Steve Bauman
     *
     * @return object
     */
    public function get($select = array('*'))
    {
        
        return $this->model->select($select)->get();
        
    }
	
    /**
     * Apply distinct filtering to the model
     *
     * @author Steve Bauman
     *
     * @return object
     */
    public function distinct()
    {
        
        return $this->model->distinct();
        
    }
	
    /**
     * Apply `with` relations to the model
     *
     * @author Steve Bauman
     *
     * @return object
     */
    public function with($with = array())
    {
        
        return $this->model->with($with);
        
    }
        
    /**
     * Apply `where` filtering to the model
     *
     * If no value is specified, then the operator arguement is used as the value
     * 
     * @author Steve Bauman
     *
     * @return object
     */
    public function where($column, $operator, $value = NULL)
    {
        
        if(is_null($value)){
            
            return $this->model->where($column, $operator);
            
        } else{
            
            return $this->model->where($column, $operator, $value);
            
        }

    }
    
    /**
     * Create a record through eloquent mass assignment
     * 
     * @return boolean OR object
     */
    public function create()
    {
        
        $this->startTransaction();
        
        try {
            
            $record = $this->model->create($this->input);
            
            if($record) {
                $this->dbCommitTransaction();
            
                return $record;
            }
            
            $this->dbRollbackTransaction();
            
            return false;
            
        } catch(Exception $e) {
            
            $this->dbRollbackTransaction();
            
            return false;
        }
    }
    
    /**
     * Update a record through eloquent mass assignment
     * 
     * @param type $id
     * @return boolean OR object
     */
    public function update($id)
    {
        
        $this->dbStartTransaction();
        
        try{
            
            $record = $this->find($id);

            if($record->update($this->input)){
                
                $this->dbCommitTransaction();
                
                return $record;
                
            }
            
            $this->dbRollbackTransaction();
            
            return false;
        
        } catch(Exception $e) {
            
            $this->dbRollbackTransaction();
            
        }
    }


    /**
     * Apply order by sorting to the model
     *
     * @author Steve Bauman
     *
     * @return object
     */
    public function orderBy($column, $direction = NULL)
    {
        
        return $this->model->orderBy($column, $direction);
        
    }
    
    /**
     * Apply group by sorting to the model
     * 
     * @param type $column
     * @return type
     */
    public function groupBy($column)
    {
        
        return $this->model->groupBy($column);
        
    }
	
    /**
     * Find a record by ID
     *
     * @author Steve Bauman
     *
     * @param $id (int/string)
     * @return object
     */
    public function find($id)
    {
        $record = $this->model->find($id);

        if($record){
            return $record;
        } else{
            throw new $this->notFoundException;
        }
    }
    
    /**
     * Find a deleted record by ID
     * 
     * @param type $id
     * @return type
     * @throws type
     */
    public function findArchived($id)
    {
        $record = $this->model->withTrashed()->find($id);
        
        if($record){
            return $record;
        } else{
            throw new $this->notFoundException;
        }
    }
    
    /**
     * Destroy a record from given ID
     *
     * @author Steve Bauman
     *
     * @param $id (int/string)
     * @return boolean
     */
    public function destroy($id)
    {
        if($this->model->destroy($id)){
            return true;
        }
        
        return false;
    }
    
    /**
     * Destroy a soft deleted record by ID
     * 
     * @param type $id
     */
    public function destroyArchived($id)
    {
        $record = $this->findArchived($id);

        return $record->forceDelete();
    }
    
    /**
     * Restore a soft deleted record by ID
     * 
     * @param type $id
     * @return NULL
     */
    public function restoreArchived($id)
    {
        $record = $this->findArchived($id);
        
        return $record->restore();
    }
    
    /**
     * Sets the GET variable in the URL for the paginator. This allows
     * for multiple paginators on the same page.
     * 
     * @param string $name
     * @return \Stevebauman\CoreHelper\Services\AbstractModelService
     */
    public function setPaginatedName($name)
    {
        Paginator::setPageName($name);
        
        return $this;
    }
    
    /**
     * Returns the current models database table name
     * 
     * @return string
     */
    public function getTableName()
    {
        return $this->model->getCurrentTable();
    }
    
    /**
     * Starts a database transaction
     * 
     * @return void
     */
    protected function dbStartTransaction()
    {
        return DB::beginTransaction();
    }
    
    /**
     * Commits the current database transaction
     * 
     * @return void
     */
    protected function dbCommitTransaction()
    {
        return DB::commit();
    }
    
    /**
     * Rolls back a database transaction
     * 
     * @return void
     */
    protected function dbRollbackTransaction()
    {
        return DB::rollback();
    }
    
    /**
     * Formats javascript plugin 'Pickadate' and 'Pickatime' date strings into PHP dates
     * 
     * @param type $date
     * @param type $time
     * @return null OR date
     */
    protected function formatDateWithTime($date, $time = NULL)
    {
        
        if($date){
            
            if($time){
                
                return date('Y-m-d H:i:s', strtotime($date. ' ' . $time));
                
            }
                
            return date('Y-m-d H:i:s', strtotime($date));
                
        }
        
        return NULL;
    }

}