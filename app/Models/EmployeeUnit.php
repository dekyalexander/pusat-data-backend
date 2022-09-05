<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeUnit extends Model
{
    use HasFactory;
    protected $connection = "hris";

    public function employeeunitype()
    {
        return $this->belongsTo(EmployeeUnitTypes::class, 'employee_unit_type_id', 'id');
    }
}
