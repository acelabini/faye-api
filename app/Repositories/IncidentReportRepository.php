<?php

namespace App\Repositories;

use App\Models\IncidentReport;

class IncidentReportRepository extends Repository
{
    public function setModel()
    {
        $this->model = new IncidentReport();
    }

    public function getReports()
    {
        return $this->model
            ->orderBy('created_at', 'desc')
            ->get()
            ;
    }
}
