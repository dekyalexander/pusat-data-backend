<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOccupation extends Model
{
    use HasFactory;
    protected $connection = "hris";
    protected $table = "employee_occupations";

    public function employeeunitypeoccupation()
    {
        return $this->belongsTo(EmployeeUnitTypes::class, 'employee_unit_type_id', 'id');
    }
}
