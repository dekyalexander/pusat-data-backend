<?php

namespace App\Repositories;

use App\Models\EmployeeUnit;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class EmployeeUnitRepository
{

    protected $employeeUnit;

    public function __construct(EmployeeUnit $employeeUnit)
    {
        $this->employeeUnit = $employeeUnit;
    }

    public function data($request)
    {
        return  $this->employeeUnit->orderBy('name', 'ASC')
            ->when(isset($request['name']), function ($query) use ($request) {
                return $query->orWhere('name', $request['name']);
            });
    }

    public function getForOptions()
    {
        return $this->employeeUnit->select('id', 'name')->get();
    }
}
