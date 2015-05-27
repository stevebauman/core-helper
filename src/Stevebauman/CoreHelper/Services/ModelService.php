<?php

namespace Stevebauman\CoreHelper\Services;

use Exception;
use Illuminate\Support\Facades\DB;

abstract class ModelService extends Service
{
    /*
     * Holds the eloquent model to query
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /*
     * Holds the exception to be thrown when a record isn't found
     */
    protected $notFoundException;

    /**
     * Returns all model records.
     *
     * @param array $select
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($select = ['*'])
    {
        return $this->model
            ->select($select)
            ->get();
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
    public function with($with = [])
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
        if(is_null($value)) return $this->model->where($column, $operator);

        return $this->model->where($column, $operator, $value);
    }

    /**
     * Create a record through eloquent mass assignment
     *
     * @return mixed
     */
    public function create()
    {
        $this->dbStartTransaction();

        try
        {
            $record = $this->model->create($this->input);

            if($record)
            {
                $this->dbCommitTransaction();

                return $record;
            }

        } catch(Exception $e)
        {
            $this->dbRollbackTransaction();
        }

        return false;
    }

    /**
     * Alias for the Laravel firstOrCreate function involving database transactions
     *
     * @return mixed
     */
    public function firstOrCreate()
    {
        $this->dbStartTransaction();

        try
        {
            $record = $this->model->firstOrCreate($this->input);

            if($record)
            {
                $this->dbCommitTransaction();

                return $record;
            }

        } catch(Exception $e)
        {
            $this->dbRollbackTransaction();
        }

        return false;
    }

    /**
     * Update a record through eloquent mass assignment
     *
     * @param string|int $id
     * @return boolean OR object
     */
    public function update($id)
    {
        $this->dbStartTransaction();

        try
        {
            $record = $this->find($id);

            if($record->update($this->input))
            {
                $this->dbCommitTransaction();

                return $record;
            }

        } catch(Exception $e)
        {
            $this->dbRollbackTransaction();
        }

        return false;
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
     * @param string $column
     * @return mixed
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
     * @param $ids mixed
     * @return mixed
     */
    public function find($ids)
    {
        $records = $this->model->find($ids);

        if($records) return $records;

        if($this->notFoundException instanceof Exception) {
            throw new $this->notFoundException;
        }

        return null;
    }

    /**
     * Find a deleted record by ID
     *
     * @param string|int $id
     * @return mixed
     */
    public function findArchived($id)
    {
        $record = $this->model->withTrashed()->find($id);

        if($record) return $record;

        throw new $this->notFoundException;
    }

    /**
     * Destroy a record from given ID
     *
     * @author Steve Bauman
     *
     * @param string|int $id
     * @return boolean
     */
    public function destroy($id)
    {
        if($this->model->destroy($id)) return true;

        return false;
    }

    /**
     * Destroy a soft deleted record by ID
     *
     * @param string|int $id
     *
     * @return bool
     */
    public function destroyArchived($id)
    {
        $record = $this->findArchived($id);

        return $record->forceDelete();
    }

    /**
     * Restore a soft deleted record by ID
     *
     * @param string|int $id
     * @return mixed
     */
    public function restoreArchived($id)
    {
        $record = $this->findArchived($id);

        return $record->restore();
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
     * @param string $date
     * @param string $time
     * @return null OR date
     */
    protected function formatDateWithTime($date, $time = NULL)
    {
        if($date)
        {
            if($time) return date('Y-m-d H:i:s', strtotime($date. ' ' . $time));

            return date('Y-m-d H:i:s', strtotime($date));
        }

        return NULL;
    }
}