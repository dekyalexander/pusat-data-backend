<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class EmployeeCareer extends Model
{
    use HasFactory;

    protected $connection = "hris";

    protected $table = "employee_careers";

    public function positions()
    {
        return $this->belongsTo(EmployeePosition::class, 'employee_position_id', 'id');
    }

    public function occupation()
    {
        return $this->belongsTo(EmployeeOccupation::class, 'employee_occupation_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
