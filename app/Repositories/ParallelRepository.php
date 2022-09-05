<?php

namespace App\Repositories;

use App\Models\Parallel;
use App\Models\KelasTK;
use App\Models\KelasSD;
use App\Models\KelasSMP;
use App\Models\KelasSMA;
use App\Models\StudentSMA;
use App\Models\StudentTK;
use Carbon\Carbon;

class ParallelRepository
{
  protected $parallel;

  public function __construct(Parallel $parallel)
  {
    $this->parallel = $parallel;
  }

  // public function getAllParallel(){
  //   return $this->parallel;
  // }

  public function getAllParallel($jenjang_id, $school_id)
  {
    return Parallel::where('jenjang_id', $jenjang_id)
      ->where('school_id', $school_id);
  }

  public function getParallelWithKelas()
  {
    return  Parallel::with([
      'kelas:kelases.id,kelases.name'
    ]);
  }

  public function getParallelById($id, $selects = ['*'])
  {
    return Parallel::select($selects)
      ->where('id', '=', $id);
  }

  public function getParallelByNameAndKelasId($name, $kelas_id)
  {
    return Parallel::where('name', '=', $name)
      ->where('kelas_id', '=', $kelas_id);
  }

  public function getParallelByNameAndKelasIdAndJurusanID($name, $kelas_id, $jurusan)
  {
    return Parallel::where('name', '=', $name)
      ->where('kelas_id', '=', $kelas_id)
      ->where('jurusan_id', '=', $jurusan);
  }

  public function getParallelByNameAndJenjangId($name, $jenjang_id)
  {
    return Parallel::where('name', '=', $name)
      ->where('jenjang_id', '=', $jenjang_id);
  }

  // deoadd
  public function getParallelBySchoolIdAndJenjangIdAndKelasId($school_id, $jenjang_id, $kelas_id)
  {
    return Parallel::where('school_id', '=', $school_id)
      ->where('jenjang_id', '=', $jenjang_id)
      ->where('kelas_id', '=', $kelas_id);
  }

  public function getJurusanBySchoolIdAndJenjangIdAndParallel($school_id, $jenjang_id, $parallel, $kelas_id)
  {
    return Parallel::where('school_id', '=', $school_id)
      ->where('jenjang_id', '=', $jenjang_id)
      ->where('kelas_id', '=', $kelas_id)
      ->where('name', '=', $parallel);
  }

  public function getParallelsByFilters($filters)
  {
    return  Parallel::with([
      'jenjang:jenjangs.id,jenjangs.name',
      'school:schools.id,schools.name',
      'kelas:kelases.id,kelases.name',
      'jurusan:jurusans.id,jurusans.code,jurusans.name',
    ])
      ->when(isset($filters['keyword']), function ($query) use ($filters) {
        return $query->orWhere('name', 'like', '%' . $filters['keyword'] . '%');
      })
      ->when(isset($filters['name']), function ($query) use ($filters) {
        return $query->where('name', 'like', '%' . $filters['name'] . '%');
      });
  }

  public function getParallelOptions($filters)
  {
    return Parallel::with('jurusan')
      ->when(isset($filters['name']), function ($query) use ($filters) {
        return $query->where('name', 'like', '%' . $filters['name'] . '%');
      })
      ->when(isset($filters['kelas_id']), function ($query) use ($filters) {
        return $query->where('kelas_id', '=', $filters['kelas_id']);
      });
  }

  public function getParallelTK()
  {
    return StudentTK::select('level', 'kelas')
      ->whereNotNull('level')
      ->where('level', '<>', '')
      ->whereNotNull('kelas')
      ->where('kelas', '<>', '')
      ->where('active', 1)
      ->groupBy('kelas');
  }

  // deoadd
  public function getParallelsTK($tahun)
  {
    return StudentTK::select('id', 'level', 'kelas')
      ->where('active', 1)
      ->where('tahun_ajaran', $tahun)
      ->groupBy('kelas');
  }

  public function getParallelSD($tahun)
  {
    return KelasSD::select('id', 'kelas', 'paralel', 'jurusan')
      ->groupBy('paralel');
  }

  public function getParallelSMP($tahun)
  {
    return KelasSMP::select('id', 'kelas', 'paralel', 'tahun_ajaran')
      ->where('tahun_ajaran', $tahun);
  }

  public function getParallelSMA($tahun)
  {
    return KelasSMA::with(['jurusanSMA']);
  }

  public function insertParallel($data)
  {
    Parallel::insert($data);
  }

  public function insertParallelGetId($data)
  {
    return Parallel::insertGetId($data);
  }

  public function insertGetParallel($data)
  {
    return Parallel::create($data);
  }

  public function updateParallel($data, $id)
  {
    Parallel::where('id', $id)
      ->update($data);
  }

  public function deleteParallels($ids)
  {
    Parallel::whereIn('id', $ids)
      ->delete();
  }
}
