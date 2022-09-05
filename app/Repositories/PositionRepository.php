<?php

namespace App\Repositories;

use App\Models\Position;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class PositionRepository
{

    protected $position;

    public function __construct(Position $position)
    {
        $this->position = $position;
    }

    public function getPositionByUnitId($filters, $tahun)
    {
        return $this->position->when(isset($filters['unitId']), function ($query) use ($filters) {
            return $query->where('employee_unit_id', $filters['unitId']);
        })->when(isset($filters['occupationId']), function ($query) use ($filters) {
            return $query->where('employee_occupation_id', $filters['occupationId']);
        })->where("tahun_pelajaran", $tahun);
    }

    public function getOccupationsId($filters, $tahun)
    {
        return $this->position->with([
            'occupations'
        ])->where('employee_unit_id', $filters['unitId'])->where("tahun_pelajaran", $tahun)->groupBy("employee_occupation_id");
    }
}
