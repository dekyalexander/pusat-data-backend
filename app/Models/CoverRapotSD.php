<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
id	int(11) AI PK
tahunajaran	varchar(15)
nis	varchar(10)
nama	varchar(100)
kelas_id	int(11)
kelas	varchar(100)
nik_walikelas	varchar(10)
wali_kelas	varchar(100)
user_input	varchar(15)
date_input	datetime
*/

class CoverRapotSD extends Model
{
    protected $connection   = 'nilai_sdk13';
    protected $table        = 'ms_siswa_cover';
    protected $primaryKey   = 'no';
    protected $keyType      = 'int';
    public    $incrementing =  false;

    public function studentKelasSD()
    {
        return $this->hasOne(StudentKelasSD::class, 'nis', 'nis');
    }
}
