<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;
    protected $connection = "hris";
    protected $table = "employee_positions";


    public function occupations()
    {
        return $this->belongsTo(EmployeeOccupation::class, 'employee_occupation_id', 'id');
    }
}
