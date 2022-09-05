<?php

namespace App\Repositories;

use App\Models\EmployeeHRIS;
use App\Models\EmployeeOccupation;

class EmployeeOccupationRepository
{
    protected $employeeOccupation;

    public function __construct(EmployeeOccupation $employeeOccupation)
    {
        $this->employeeOccupation = $employeeOccupation;
    }

    public function getByOption($filters)
    {
        return $this->employeeOccupation->when(isset($filters['id']), function ($query) use ($filters) {
            return $query->where('id', '=', $filters['id']);
        });
    }
}
