<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHRIS extends Model
{
    protected $connection   = 'hris';
    protected $table        = 'employees';
    protected $primaryKey   = 'emp_id';
    protected $keyType      = 'string';
    public    $incrementing =  false;
}
