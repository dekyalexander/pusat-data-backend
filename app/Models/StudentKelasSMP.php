<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentKelasSMP extends Model
{
    protected $connection   = 'nilai_smp';
    protected $table        = 'set_siswa_kelas';
    protected $primaryKey   = 'id';
    protected $keyType      = 'int';
    public    $incrementing =  false;

    public function siswaKelas()
    {
        return $this->belongsTo('App\Models\KelasSMP', 'kelas_id');
    }

    public function siswaEdit()
    {
        return $this->hasOne(StudentSMPEdit::class, 'nis', 'nis');
    }
    public function siswaNisn()
    {
        return $this->hasOne(StudentSMPNisn::class, 'nis', 'nis');
    }

    public function studentSMP()
    {
        return $this->hasOne(StudentSMP::class, 'nis', 'nis');
    }
}
