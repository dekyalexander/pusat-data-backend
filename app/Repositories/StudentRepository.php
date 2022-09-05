<?php

namespace App\Repositories;

use App\Models\Student;
use App\Models\StudentTK;
use App\Models\StudentSD;
use App\Models\StudentSMP;
use App\Models\StudentKelasSMP;
use App\Models\StudentSMA;
use App\Models\StudentPCI;
use App\Models\StudentKelasSD;
use App\Models\StudentMutation;
use App\Models\StudentSiswaSD;
use Carbon\Carbon;

class StudentRepository
{
  protected $student;

  public function __construct(Student $student)
  {
    $this->student = $student;
  }

  public function getStudentById($id, $selects = ['*'])
  {
    return Student::select($selects)
      ->where('id', '=', $id);
  }

  public function getStudentByNIY($niy)
  {
    return Student::where('niy', $niy);
  }

  public function getStudentDetail($student_id)
  {
    return Student::with([
      'sex_type:parameters.value,parameters.name',
      'religion:parameters.value,parameters.name',
      'stay_with:parameters.value,parameters.name',
      'student_status:parameters.value,parameters.name',
      'jenjang:jenjangs.id,jenjangs.name',
      'masuk_jenjang:jenjangs.id,jenjangs.name',
      'school:schools.id,schools.name',
      'kelas:kelases.id,kelases.name',
      'masuk_kelas:kelases.id,kelases.name',
      'parallel:parallels.id,parallels.name',
      'masuk_tahun:tahun_pelajarans.id,tahun_pelajarans.name',
      'lulus_tahun:tahun_pelajarans.id,tahun_pelajarans.name',
    ])
      ->where('id', '=', $student_id);
  }

  public function getStudentInIds($student_ids)
  {
    return Student::whereIn('id', $student_ids);
  }

  public function getStudentsByParentId($parent_id)
  {

    return Student::where('father_parent_id', '=', $parent_id)
      ->orWhere('mother_parent_id', '=', $parent_id);
  }

  public function getStudentsByFilters($filters)
  {
    return
      Student::with([
        'user',
        'jenjang',
        'kelas',
        'parallel',
        'parallel.jurusan',
        'parent_mother:parents.id,parents.name,parents.ktp,parents.mobilePhone',
        'parent_father:parents.id,parents.name,parents.ktp,parents.mobilePhone',
        'sex_type:parameters.value,parameters.name',
        'masuk_tahun:tahun_pelajarans.id,tahun_pelajarans.name',
        'student_status:parameters.value,parameters.name'
      ])
      ->when(isset($filters['keyword']), function ($query) use ($filters) {
        return $query
          ->orWhere('name', 'like', '%' . $filters['keyword'] . '%')
          ->orWhere('nis', 'like', '%' . $filters['keyword'] . '%')
          ->orWhere('niy', 'like', '%' . $filters['keyword'] . '%')
          ->orWhere('nkk', 'like', '%' . $filters['keyword'] . '%')
          ->orWhere('father_ktp', 'like', '%' . $filters['keyword'] . '%')
          ->orWhere('mother_ktp', 'like', '%' . $filters['keyword'] . '%');
      })
      ->when(isset($filters['is_sibling']), function ($query) use ($filters) {
        return $query->where('is_sibling_student', 1)
          ->with([
            'siblings_from_father:students.id,students.father_parent_id,students.mother_parent_id,students.nkk,students.name,students.niy,students.jenjang_id,students.kelas_id,students.parallel_id',
            'siblings_from_mother:students.id,students.father_parent_id,students.mother_parent_id,students.nkk,students.name,students.niy,students.jenjang_id,students.kelas_id,students.parallel_id',
            'siblings_from_kk:students.id,students.father_parent_id,students.mother_parent_id,students.nkk,students.name,students.niy,students.jenjang_id,students.kelas_id,students.parallel_id',
            'siblings_from_father.jenjang',
            'siblings_from_father.kelas',
            'siblings_from_father.parallel',
            'siblings_from_mother.jenjang',
            'siblings_from_mother.kelas',
            'siblings_from_mother.parallel',
            'siblings_from_kk.jenjang',
            'siblings_from_kk.kelas',
            'siblings_from_kk.parallel'
          ]);
      })
      ->when(isset($filters['school_id']), function ($query) use ($filters) {
        return $query->where('school_id', $filters['school_id']);
      })
      ->when(isset($filters['kelas_id']), function ($query) use ($filters) {
        return $query->where('kelas_id', $filters['kelas_id']);
      })
      ->when(isset($filters['parallel_id']), function ($query) use ($filters) {
        return $query->where('parallel_id', $filters['parallel_id']);
      })
      ->when(isset($filters['parent_id']), function ($query) use ($filters) {
        return $query->where('father_parent_id', $filters['parent_id'])
          ->orWhere('mother_parent_id', $filters['parent_id']);
      })
      ->when(isset($filters['student_id']), function ($query) use ($filters) {
        return $query->where('id', $filters['student_id']);
      });
  }

  public function getStudentOptions($filters)
  {
    return Student::select('id', 'name')
      ->when(isset($filters['name']), function ($query) use ($filters) {
        return $query->where('name', 'like', '%' . $filters['name'] . '%');
      });
  }

  public function getStudentHasSibling()
  {
    return Student::where('is_sibling_student', '=', 1);
  }

  public function getStudentMutationNiyYear($niy, $tahun_pelajaran_id)
  {
    return StudentMutation::where('niy', $niy)
      ->where('tahun_pelajaran_id', $tahun_pelajaran_id);
  }

  public function getSiblingByParentsId($niy, $father_parent_id, $mother_parent_id)
  {
    return Student::with([
      'jenjang',
      'kelas',
      'parallel',
      'student_status:parameters.value,parameters.name'
    ])
      ->where('niy', '<>', $niy)
      ->where(function ($query) use ($father_parent_id, $mother_parent_id) {
        $query
          ->where('father_parent_id', '=', $father_parent_id)
          ->orWhere('mother_parent_id', '=', $mother_parent_id);
      });
  }

  public function getSiblingByMotherId($niy, $mother_parent_id)
  {
    return Student::where('niy', '<>', $niy)
      ->where('mother_parent_id', '=', $mother_parent_id);
  }

  public function getSiblingByFatherId($niy, $father_parent_id)
  {
    return Student::where('niy', '<>', $niy)
      ->where('father_parent_id', '=', $father_parent_id);
  }

  public function getAllStudents()
  {
    return $this->student;
  }

  public function getStudentsByJenjang($jenjang_id)
  {
    return Student::where('jenjang_id', $jenjang_id);
  }

  // deoadd
  public function getStudentWithBukuIndukTK($tahun)
  {
    return StudentTK::with(['bukuInduk'])
      ->select('id', 'nis', 'nama', 'kelas', 'level', 'jenjang', 'tahun_ajaran', 'tgl_lahir')
      ->where('active', 1)
      ->where('tahun_ajaran', $tahun)
      ->has('bukuInduk');
  }

  public function getStudentSDWithBukuInduk($tahun)
  {
    return StudentSD::with([
      'studentSiswaSD',
      'coverRapotSD',
      'coverRapotSD.studentKelasSD',
      'studentKelasSD',
      'studentKelasSD.kelasSD'
    ])
      ->where('tahunAjaran', $tahun)
      ->has('studentSiswaSD')
      ->has('coverRapotSD');
  }

  public function getStudentSDAndKelasWithBukuInduk($tahun)
  {

    return StudentKelasSD::with([
      'coverKelas',
      'studentSD',
      'kelasSD'
    ])
      ->where('tahunajaran', $tahun);

    // return StudentKelasSD::select('set_siswa_kelas.*', 'vw_ms_siswa_cover.nisn', 'vw_siswa.*', 'ms_kelas.kelas', 'ms_kelas.paralel')
    //   ->leftJoin('vw_ms_siswa_cover', 'vw_ms_siswa_cover.nis', '=', 'set_siswa_kelas.nis')
    //   ->leftJoin('vw_siswa', 'set_siswa_kelas.no_induk_pahoa', '=', 'vw_siswa.nomorinduksiswa')
    //   ->leftJoin('ms_kelas', 'set_siswa_kelas.kelas_id', 'ms_kelas.id')
    //   ->where('set_siswa_kelas.tahunajaran', $tahun);
  }

  public function getStudentSMPWithBukuInduk($tahun)
  {
    return StudentKelasSMP::with([
      'siswaKelas',
      'studentSMP',
      'siswaNisn',
      'siswaEdit'
    ])
      ->where('tahunajaran', $tahun)
      ->whereHas('studentSMP', function ($query) {
        $query->where('active', '=', 1);
      })
      ->has('siswaEdit')
      ->has('studentSMP');
  }

  public function getStudentSMPWithTahunAjaran($tahun)
  {
    return StudentSMP::select('ms_siswa.nis', 'ms_siswa.nama', 'ms_siswa.active', 'ms_siswa_nisn.nisn', 'ms_siswa_nisn.niy', 'ms_siswa_nisn.warga_negara', 'set_siswa_kelas.tahunajaran', 'set_siswa_kelas.kelas_id', 'ms_kelas.*', 'ms_siswa_edit.sekolah_asal', 'ms_siswa_edit.agama', 'ms_siswa_edit.tmp_lahir', 'ms_siswa_edit.jenis_kelamin', 'ms_siswa_edit.tgl_lahir', 'ms_siswa_edit.anak_ke', 'ms_siswa_edit.alamat', 'ms_siswa_edit.sekolah_asal', 'ms_siswa_edit.nama_ayah', 'ms_siswa_edit.nama_ibu', 'ms_siswa_edit.pek_ayah', 'ms_siswa_edit.pek_ibu', 'ms_siswa_edit.alamat_ortu', 'ms_siswa_edit.ktpAyah', 'ms_siswa_edit.ktpIbu', 'ms_siswa_edit.no_kk', 'ms_siswa_edit.kkAyah', 'ms_siswa_edit.kkIbu', 'ms_siswa_edit.kkSiswa')
      ->join('set_siswa_kelas', 'ms_siswa.nis', '=', 'set_siswa_kelas.nis')
      ->leftJoin('ms_siswa_nisn', 'ms_siswa.nis', '=', 'ms_siswa_nisn.nis')
      ->leftJoin('ms_kelas', 'set_siswa_kelas.kelas_id', '=', 'ms_kelas.id')
      ->leftJoin('ms_siswa_edit', 'ms_siswa.nis', '=', 'ms_siswa_edit.nis')
      ->where('ms_siswa.active', '=', 1)
      ->where('set_siswa_kelas.tahunajaran', $tahun);
  }

  public function getStudentSMAWithBukuInduk($tahun)
  {
    return StudentSMA::with([
      'bukuInduk',
      'kelasSMA'
    ])
      ->where('tahunajaran', $tahun)
      ->where('status', 1);
  }

  public function getStudentKelasSD($tahun)
  {
    return StudentKelasSD::where('tahunajaran', $tahun);
  }

  public function getStudentSD($tahun)
  {
    return StudentSD::select('id', 'nis', 'nama', 'kelas_id', 'kelas', 'tahunajaran')
      ->where('tahunajaran', $tahun)
      ->where('nomor_induk_pahoa', '81122047');
  }

  public function getStudentSMP($tahun)
  {
    return StudentSMP::select('id', 'nama');
  }

  public function getStudentSMA($tahun)
  {
    return StudentSMA::select('id', 'nis', 'niy', 'nin', 'nama', 'jenjang', 'kelas', 'tahunajaran')
      ->where('tahunajaran', $tahun);
  }

  public function getStudentSMAs($tahun)
  {
    return StudentSMA::select('mst_sma_siswa.*', 'mst_sma_kelas.kelas', 'mst_sma_kelas.pararel', 'mst_sma_jurusan.nama_jurusan')
      ->join('mst_sma_kelas', 'mst_sma_siswa.kelas', '=', 'mst_sma_kelas.id')
      ->join('mst_sma_jurusan', 'mst_sma_kelas.jurusan', '=', 'mst_sma_jurusan.id')
      ->where('mst_sma_siswa.tahunajaran', $tahun);
  }

  public function getStudentPCI($tahun)
  {
    return StudentPCI::select('id', 'nis', 'nama', 'kelas', 'jenjang', 'tahun_ajaran')
      ->where('tahun_ajaran', $tahun);
  }

  public function getMutationsOfStudent($niy)
  {
    return StudentMutation::with([
      'jenjang',
      'school',
      'kelas',
      'parallel',
      'student_status:parameters.value,parameters.name',
      'tahunPelajaran'
    ])
      ->where('niy', $niy);
  }


  public function insertStudent($data)
  {
    Student::insert($data);
  }

  public function insertStudentMutation($data)
  {
    StudentMutation::insert($data);
  }

  public function insertStudentGetId($data)
  {
    return Student::insertGetId($data);
  }

  public function insertGetStudent($data)
  {
    return Student::create($data);
  }

  public function updateStudent($data, $id)
  {
    Student::where('id', $id)
      ->update($data);
  }

  public function updateStudents($data, $ids)
  {
    Student::whereIn('id', $ids)
      ->update($data);
  }


  public function updateStudentMutation($data, $id)
  {
    StudentMutation::where('id', $id)
      ->update($data);
  }

  public function updateStudentByNIY($data, $niy)
  {
    Student::where('niy', $niy)
      ->update($data);
  }

  public function updateStudentByNIS($data, $nis)
  {
    Student::where('nis', $nis)
      ->update($data);
  }

  public function deleteStudents($ids)
  {
    Student::whereIn('id', $ids)
      ->delete();
  }
}
