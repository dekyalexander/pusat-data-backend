<?php

namespace App\Repositories;

use App\Models\EmployeeCareer;

class EmployeeCareerRepository
{
    protected $employeeCareer;

    public function __construct(EmployeeCareer $employeeCareer)
    {
        $this->employeeCareer = $employeeCareer;
    }

    public function getEmployeeCareers($user_id)
    {
        return $this->employeeCareer
            ->where('employee_id', $user_id)
            ->with([
                'positions:id,mpp_information_id,tahun_pelajaran,code,name,parent,employee_occupation_id,employee_unit_id',
                'positions.employeeunit:id,name,employee_unit_type_id',
                'positions.employeeunit.employeeunitype:id,name',
                'occupation:id,name,employee_unit_type_id',
                'occupation.employeeunitypeoccupation:id,name',
            ])->get();
    }

    public function getByOption($filters)
    {
        return $this->employeeCareer->with(['user.user'])
            ->when(isset($filters['unitId']), function ($query) use ($filters) {
                return $query->where('employee_unit_id', '=', $filters['unitId']);
            })
            ->when(isset($filters['positionId']), function ($query) use ($filters) {
                return $query->where('employee_position_id', '=', $filters['positionId']);
            })->when(isset($filters['positionCode']), function ($query) use ($filters) {
                return $query->where('employee_position_code', '=', $filters['positionCode']);
            })->when(isset($filters['occupationId']), function ($query) use ($filters) {
                return $query->where('employee_occupation_id', '=', $filters['occupationId']);
            })
            ->where("status", 1);
    }
}
