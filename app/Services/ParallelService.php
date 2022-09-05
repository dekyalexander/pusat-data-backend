<?php

namespace App\Services;

use App\Repositories\ParallelRepository;
use App\Repositories\TahunPelajaranRepository;
use App\Repositories\JenjangRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\KelasRepository;
use App\Repositories\JurusanRepository;
use App\Repositories\StudentRepository;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class ParallelService
{
  protected $studentRepository;
  protected $parallelRepository;
  protected $tahunPelajaranRepository;
  protected $jenjangRepository;
  protected $schoolRepository;
  protected $kelasRepository;
  protected $jurusanRepository;

  public function __construct(StudentRepository $studentRepository, JurusanRepository $jurusanRepository, SchoolRepository $schoolRepository, JenjangRepository $jenjangRepository, KelasRepository $kelasRepository, TahunPelajaranRepository $tahunPelajaranRepository, ParallelRepository $parallelRepository)
  {
    $this->parallelRepository = $parallelRepository;
    $this->kelasRepository = $kelasRepository;
    $this->tahunPelajaranRepository = $tahunPelajaranRepository;
    $this->jenjangRepository = $jenjangRepository;
    $this->schoolRepository = $schoolRepository;
    $this->jurusanRepository = $jurusanRepository;
    $this->studentRepository = $studentRepository;
  }

  public function getParallelsByFilters($filters)
  {
    return $this->parallelRepository
      ->getParallelsByFilters($filters)
      ->get();
  }

  public function getByFiltersPagination($filters, $rowsPerPage = 25)
  {
    return $this->parallelRepository
      ->getParallelsByFilters($filters)
      ->paginate($rowsPerPage);
  }

  public function getParallelOptions($filters)
  {
    return $this->parallelRepository->getParallelOptions($filters)->get();
  }

  public function syncParallelTK()
  {
    $jenjang = $this->jenjangRepository->getJenjangByCode('TK')->first();
    $school = $this->schoolRepository->getSchoolByJenjang($jenjang->id)->first();
    $tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();

    $existingParallels = $this->parallelRepository->getAllParallel($jenjang->id, $school->id)->pluck('name')->all();
    $parallels = $this->parallelRepository->getParallelsTK($tahunActive->name)->get();

    foreach ($parallels as $parallel) {
      if (!in_array(trim($parallel->kelas), $existingParallels)) {
        $kelasId = $this->kelasRepository->getKelasByNameAndJenjangId(trim($parallel->level), $jenjang->id)->value('id');
        $this->parallelRepository->insertParallel(
          [
            'tahun_pelajaran' => $tahunActive->name,
            'jenjang_id' => $jenjang->id,
            'school_id' => $school->id,
            'kelas_id' => $kelasId,
            'jurusan_id' => null,
            'name' => trim($parallel->kelas),
            'code' => trim($parallel->kelas)
          ]
        );
      }
    }
    return response([
      'success' => true,
      'message' => 'success',
    ], 200);
  }

  public function syncParallelSD()
  {
    $jenjang = $this->jenjangRepository->getJenjangByCode('SD')->first();
    $school = $this->schoolRepository->getSchoolByJenjang($jenjang->id)->first();
    $tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();
    $existingParallels = $this->parallelRepository->getAllParallel($jenjang->id, $jenjang->id)->pluck('name')->all();
    $parallels = $this->parallelRepository->getParallelSD($tahunActive->name)->get();
    foreach ($parallels as $parallel) {
      if (!in_array($parallel->paralel, $existingParallels)) {
        $kelasId = $this->kelasRepository->getKelasByNameAndJenjangId($parallel->kelas, $jenjang->id)->select('id', 'name')->value('id');
        $this->parallelRepository->insertParallel(
          [
            'tahun_pelajaran' => $tahunActive->name,
            'jenjang_id' => $jenjang->id,
            'school_id' => $school->id,
            'kelas_id' => $kelasId,
            'jurusan_id' => null,
            'name' => $parallel->paralel,
            'code' => $parallel->paralel
          ]
        );
      }
    }
    return response([
      'success' => true,
      'message' => 'success',
    ], 200);
  }

  public function syncParallelSMP()
  {
    $jenjang = $this->jenjangRepository->getJenjangByCode('SMP')->first();
    $school = $this->schoolRepository->getSchoolByJenjang($jenjang->id)->first();
    $tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();
    $parallels = $this->parallelRepository->getParallelSMP($tahunActive->name)->get();
    // dd($parallels);
    foreach ($parallels as $parallel) {
      // dd($parallel->paralel);
      $parallelNum = $this->getKelasName($parallel->kelas);
      // dd($parallelNum);
      // deoadd
      $kelasId = $this->kelasRepository->getKelasByNameAndJenjangId($parallelNum, $jenjang->id)->select('id', 'name')->value('id');

      $existingParallels = $this->parallelRepository->getParallelBySchoolIdAndJenjangIdAndKelasId($school->id, $jenjang->id, $kelasId)->pluck('name')->all();

      if (!in_array($parallel->paralel, $existingParallels)) {
        $this->parallelRepository->insertParallel(
          [
            'tahun_pelajaran' => $tahunActive->name,
            'jenjang_id' => $jenjang->id,
            'school_id' => $school->id,
            'kelas_id' => $kelasId,
            'jurusan_id' => null,
            'name' => $parallel->paralel,
            'code' => $parallel->paralel
          ]
        );
      }
    }
    return response([
      'success' => true,
      'message' => 'success',
    ], 200);
  }

  public function syncParallelSMA()
  {
    $jenjang = $this->jenjangRepository->getJenjangByCode('SMA')->first();
    $school = $this->schoolRepository->getSchoolByJenjang($jenjang->id)->first();
    $tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();

    $parallels = $this->parallelRepository->getParallelSMA($tahunActive->name)->get();
    $studentSMA = $this->studentRepository->getStudentSMAs($tahunActive->name)->get();

    foreach ($studentSMA as $studentSMA) {
      // dd($studentSMA);
      $kelasId = $this->kelasRepository->getKelasByNameAndJenjangId($this->getKelasName($studentSMA->kelas), $jenjang->id)->select('id', 'name')->value('id');
      $existingParallels = $this->parallelRepository->getParallelBySchoolIdAndJenjangIdAndKelasId($school->id, $jenjang->id, $kelasId)->pluck('name')->all();
      $existingJurusan = $this->parallelRepository->getJurusanBySchoolIdAndJenjangIdAndParallel($school->id, $jenjang->id, $studentSMA->pararel, $kelasId)->pluck('jurusan_id')->all();
      $jurusanId = $this->jurusanRepository->getJurusanByName($studentSMA->nama_jurusan)->value('id');
      // dd($existingJurusan);
      if (!in_array($studentSMA->pararel, $existingParallels)) {

        if ($studentSMA->nama_jurusan !== null) {
          // dd($jurusanId);
          if ($jurusanId) {
            // dd($kelasId);
            $this->parallelRepository->insertParallel(
              [
                'tahun_pelajaran' => $tahunActive->name,
                'jenjang_id' => $jenjang->id,
                'school_id' => $school->id,
                'kelas_id' => $kelasId,
                'jurusan_id' => $jurusanId,
                'name' => $studentSMA->pararel,
                'code' => $studentSMA->pararel
              ]
            );
          }
        }
      } else {
        if (!in_array($jurusanId, $existingJurusan)) {
          if ($studentSMA->nama_jurusan !== null) {
            // dd($jurusanId);
            if ($jurusanId) {
              // dd($kelasId);
              $this->parallelRepository->insertParallel(
                [
                  'tahun_pelajaran' => $tahunActive->name,
                  'jenjang_id' => $jenjang->id,
                  'school_id' => $school->id,
                  'kelas_id' => $kelasId,
                  'jurusan_id' => $jurusanId,
                  'name' => $studentSMA->pararel,
                  'code' => $studentSMA->pararel
                ]
              );
            }
          }
        }
      }
    }
    return response([
      'success' => true,
      'message' => 'success',
    ], 200);
  }

  public function syncParallelPCI()
  {
    $jenjang = $this->jenjangRepository->getJenjangByCode('PCI')->first();
    $school = $this->schoolRepository->getSchoolByJenjang($jenjang->id)->first();
    $tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();
    $existingParallels = $this->parallelRepository->getAllParallel()->pluck('name')->all();
    $parallels = $this->parallelRepository->getParallelPCI($tahunActive->name)->get();

    foreach ($parallels as $parallel) {
      if (!in_array($parallel->paralel, $existingParallels)) {
        $kelasId = $this->kelasRepository->getKelasByNameAndJenjangId($parallel->kelas, $jenjang->id)->select('id', 'name')->value('id');
        $this->parallelRepository->insertParallel(
          [
            'jenjang_id' => $jenjang->id,
            'school_id' => $school->id,
            'kelas_id' => $kelasId,
            'jurusan_id' => null,
            'name' => $parallel->paralel,
            'code' => $parallel->paralel
          ]
        );
      }
    }
  }

  public function createParallel($data)
  {
    $this->parallelRepository->insertParallel($data);
  }

  public function updateParallel($data, $id)
  {
    $this->parallelRepository->updateParallel($data, $id);
  }

  public function deleteParallels($ids)
  {
    $this->parallelRepository->deleteParallels($ids);
  }
  function getKelasName($kelasName)
  {
    if ($kelasName === 'I') {
      return '1';
    } elseif ($kelasName === 'II') {
      return '2';
    } elseif ($kelasName === 'III') {
      return '3';
    } elseif ($kelasName === 'IV') {
      return '4';
    } elseif ($kelasName === 'V') {
      return '5';
    } elseif ($kelasName === 'VI') {
      return '6';
    } elseif ($kelasName === 'VII') {
      return '7';
    } elseif ($kelasName === 'VIII') {
      return '8';
    } elseif ($kelasName === 'IX') {
      return '9';
    } elseif ($kelasName === 'X') {
      return '10';
    } elseif ($kelasName === 'XI') {
      return '11';
    } elseif ($kelasName === 'XII') {
      return '12';
    } else {
      return $kelasName;
    }
  }
}
