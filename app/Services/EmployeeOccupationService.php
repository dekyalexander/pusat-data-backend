<?php

namespace App\Services;

use App\Models\EmployeeCareer;
use App\Repositories\EmployeeCareerRepository;
use App\Repositories\EmployeeOccupationRepository;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class EmployeeOccupationService
{
    protected $employeeOccupationRepo;

    public function __construct(EmployeeOccupationRepository $employeeOccupationRepo)
    {
        $this->employeeOccupationRepo = $employeeOccupationRepo;
    }

    public function getByOption($filters)
    {
        return $this->employeeOccupationRepo->getByOption($filters);
    }
}
