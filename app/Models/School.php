<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
  
    public function jenjang(){
        return $this->belongsTo('App\Models\Jenjang', 'jenjang_id');
    }

    public function head_employee(){
        return $this->belongsTo('App\Models\Employee', 'head_employee_id');
      }
}
