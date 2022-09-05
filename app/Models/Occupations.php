<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\FuncCall;

class Occupations extends Model
{
    use HasFactory;

    protected $table = 'occupations';


    public function user(){
        return $this->belongsTo(User::class);
    } 

    // public function users()
    // {
    //     return $this->belongsToMany('User', 'occupations_user')->withPivot('unit_id');
    // }

    // public function unit(){
    //     return $this->belongsToMany(Unit::class,'occupations_user')->withPivot('user_id');
    // }

}



