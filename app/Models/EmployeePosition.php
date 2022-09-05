<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePosition extends Model
{
    use HasFactory;
    protected $connection = "hris";
    protected $table = "employee_positions";

    public function employeeunit()
    {
        return $this->belongsTo(EmployeeUnit::class, 'employee_unit_id', 'id');
    }
}
