<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeUnitTypes extends Model
{
    use HasFactory;
    protected $connection = "hris";
    protected $table = "employee_unit_types";
}
