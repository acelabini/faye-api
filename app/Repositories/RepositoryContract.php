<?php

namespace App\Repositories;

interface RepositoryContract
{
    public function create($parameters);

    public function get($id);

    public function update($model, $data);

    public function delete($model);

    public function destroy($ids);

    public function search($query);

    public function find($query);
}