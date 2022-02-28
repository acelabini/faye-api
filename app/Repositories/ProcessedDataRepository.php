<?php

namespace App\Repositories;

use App\Models\ProcessedData;

class ProcessedDataRepository extends Repository
{
    public function setModel()
    {
        $this->model = new ProcessedData();
    }

    public function publishByIds($ids = [])
    {
        return $this->model
            ->whereIn('id', $ids)
            ->update([
                'publish' => true
            ]);
    }

    public function getPublished()
    {
        return $this->model
            ->where('publish', true)
            ->get();
    }

    public function getAll()
    {
        return $this->model
            ->orderBy('id', 'desc')
            ->get();
    }
}
