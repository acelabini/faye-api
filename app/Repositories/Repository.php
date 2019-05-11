<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Repositories\Exceptions\RepositoryInvalidQueryFormatException;
use App\Repositories\Exceptions\RepositoryModelAttributeNotFoundException;

abstract class Repository implements RepositoryContract
{
    protected $model;

    protected $withTrashed = false;

    abstract function setModel();

    public function __construct()
    {
        $this->setModel();
    }

    public function getById($id)
    {
        return $this->model::find($id);
    }

    /**
     * Save new model
     *
     * @param  array $params
     * @return Model
     */
    public function create($params): Model
    {
        return $this->model::create($params);
    }

    /**
     * Get model by id
     *
     * @param  int $id
     * @return Model
     */
    public function get($id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Update model
     *
     * @param  Model $model
     * @param  array $data
     * @return Model
     */
    public function update($model, $data): Model
    {
        foreach ($data as $key => $value)
        {
            $model->{$key} = $value;
        }

        $model->save();

        return $model;
    }

    /**
     * Delete model record
     *
     * @param  Model $model
     * @return bool
     */
    public function delete($model)
    {
        $delete = $model->delete();

        return ($delete) ? true : false;
    }

    /**
     * Delete multiple model records
     *
     * @param  array $ids array of ids to be deleted
     * @return bool
     */
    public function destroy($ids)
    {
        return ($this->model::destroy($ids)) ? true : false;
    }

    /**
     * Search for models that match passed query
     *
     * @param  array $query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search($query)
    {
        $this->validateSearch($query);

        return $this->baseSearch($query)->get();
    }

    /**
     * Search for first model that matches passed query
     *
     * @param  array $query
     * @return Model
     */
    public function find($query)
    {
        $this->validateSearch($query);

        return $this->baseSearch($query)->firstOrFail();
    }

    public function withTrashed()
    {
        $this->withTrashed = true;

        return $this;
    }

    /**
     * Validates search query parameters
     * @param  array $query [description]
     * @return Void
     * @throws RepositoryInvalidQueryFormatException     Query passed is not an array
     * @throws RepositoryModelAttributeNotFoundException Column passed doesn't exist in model table
     */
    private function validateSearch($query)
    {
        $attributes = Schema::getColumnListing($this->model->getTable());

        foreach ($query as $search) {
            if (!is_array($search)) {
                throw new RepositoryInvalidQueryFormatException;
            }
            if (!in_array(current($search), $attributes)) {
                throw new RepositoryModelAttributeNotFoundException;
            }
        }
    }

    private function baseSearch($query)
    {

        if ($this->withTrashed) {
            $this->model = $this->model::withTrashed();
        }

        return $this->model->where($query);
    }
}