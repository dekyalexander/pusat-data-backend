<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $connection = "hris";
    protected $table = "employees";

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
