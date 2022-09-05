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

class StudentKelasSD extends Model
{
    protected $connection   = 'nilai_sdk13';
    protected $table        = 'set_siswa_kelas';
    protected $primaryKey   = 'id';
    protected $keyType      = 'int';
    public    $incrementing =  false;

    public function kelasSD()
    {
        return $this->belongsTo('App\Models\KelasSD', 'kelas_id', 'id');
    }

    public function studentSD()
    {
        return $this->hasOne('App\Models\StudentSD', 'nomorinduksiswa', 'no_induk_pahoa');
    }

    public function coverKelas()
    {
        return $this->belongsTo(CoverRapotSD::class, 'nis', 'nis');
    }
}
