<?php

namespace App\Services;

use App\Repositories\EmployeeUnitRepository;
use App\Repositories\UnitRepository;

class EmployeeUnitService
{

    protected $employeeUnitRepo;

    public function __construct(EmployeeUnitRepository $employeeUnitRepo)
    {
        $this->employeeUnitRepo = $employeeUnitRepo;
    }

    public function data($request)
    {
        $result = $this->employeeUnitRepo->data($request);
        return $result;
    }

    public function getForOptions()
    {
        return $this->employeeUnitRepo->getForOptions();
    }
}
