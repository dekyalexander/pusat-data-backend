<?php

namespace App\Services;

use App\Models\EmployeeCareer;
use App\Repositories\EmployeeCareerRepository;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class EmployeeCareerService
{
    protected $employeeCareerRepo;

    public function __construct(EmployeeCareerRepository $employeeCareerRepo)
    {
        $this->employeeCareerRepo = $employeeCareerRepo;
    }

    public function getCareerByOption($filter)
    {
        return $this->employeeCareerRepo->getByOption($filter)->get();
    }
}
