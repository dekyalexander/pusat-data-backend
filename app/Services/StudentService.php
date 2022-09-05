<?php

namespace App\Services;

use App\Repositories\StudentRepository;
use App\Repositories\ParentRepository;
use App\Repositories\TahunPelajaranRepository;
use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use App\Repositories\JenjangRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\KelasRepository;
use App\Repositories\ParallelRepository;
use App\Repositories\ParameterRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentExcel;
use App\Models\StudentSD;

class StudentService
{

  protected $studentRepository;
  protected $parentRepository;
  protected $tahunPelajaranRepository;
  protected $userRepo;
  protected $roleRepository;
  protected $jenjangRepository;
  protected $schoolRepository;
  protected $kelasRepository;
  protected $parallelRepository;
  protected $parameterRepository;
  protected $jenjang;
  protected $school;
  protected $tahunActive;
  protected $monthNames = [
    'unknown' => 0,
    'Januari' => 1,
    'Februari' => 2,
    'Maret' => 3,
    'April' => 4,
    'Mei' => 5,
    'Juni' => 6,
    'Juli' => 7,
    'Agustus' => 8,
    'September' => 9,
    'Oktober' => 10,
    'November' => 11,
    'Desember' => 12
  ];


  public function __construct(ParameterRepository $parameterRepository, ParallelRepository $parallelRepository, KelasRepository $kelasRepository, SchoolRepository $schoolRepository, JenjangRepository $jenjangRepository, ParentRepository $parentRepository,  RoleRepository $roleRepository, UserRepository $userRepository, TahunPelajaranRepository $tahunPelajaranRepository, StudentRepository $studentRepository)
  {
    $this->parentRepository = $parentRepository;
    $this->userRepository = $userRepository;
    $this->tahunPelajaranRepository = $tahunPelajaranRepository;
    $this->studentRepository = $studentRepository;
    $this->roleRepository = $roleRepository;
    $this->jenjangRepository = $jenjangRepository;
    $this->schoolRepository = $schoolRepository;
    $this->kelasRepository = $kelasRepository;
    $this->parallelRepository = $parallelRepository;
    $this->parameterRepository = $parameterRepository;
  }

  public function getStudentsByFilters($filters)
  {
    return $this->studentRepository
      ->getStudentsByFilters($filters)
      ->get();
  }

  public function getByFiltersPagination($filters, $rowsPerPage = 25)
  {
    if (isset($filters['is_sibling'])) {
      return $this->getStudentWithSiblings($this->studentRepository->getStudentsByFilters($filters), $rowsPerPage);
    } else {
      // $dd = $this->studentRepository->getStudentsByFilters($filters)->paginate($rowsPerPage);
      // dd($dd);
      return $this->studentRepository->getStudentsByFilters($filters)->paginate($rowsPerPage);
    }
  }

  public function getStudentWithSiblings($students, $rowsPerPage)
  {
    //return $this->mergeSiblings($students->paginate($rowsPerPage));
    $students = $students->paginate($rowsPerPage);

    foreach ($students as $student) {
      $siblingDuplicates = $student->siblings_from_father
        ->merge($student->siblings_from_mother)
        ->merge($student->siblings_from_kk);
      $siblings = collect();
      foreach ($siblingDuplicates as $sibling) {
        if ($sibling->id !== $student->id) {
          $siblings->push($sibling);
        }
      }
      $student->sibling_students = $siblings;
    }

    return $students;
  }

  public function mergeSiblings($studentsWithSiblings)
  {
    $students = $studentsWithSiblings;

    foreach ($students as $student) {
      $siblingDuplicates = $student->siblings_from_father
        ->merge($student->siblings_from_mother)
        ->merge($student->siblings_from_kk);
      $siblings = collect();
      foreach ($siblingDuplicates as $sibling) {
        if ($sibling->id !== $student->id) {
          $siblings->push($sibling);
        }
      }
      $student->sibling_students = $siblings;
    }

    return $students;
  }

  public function getStudentOptions($filters)
  {
    return $this->studentRepository->getStudentOptions($filters)
      ->limit(10)->get();
  }

  public function getStudentDetail($student_id)
  {
    return $this->studentRepository->getStudentDetail($student_id)->first();
  }

  public function getParentsOfStudent($student_id)
  {
    return $this->parentRepository->getParentsByStudentId($student_id)->get();
  }

  public function getMutationsOfStudent($niy)
  {
    return $this->studentRepository->getMutationsOfStudent($niy)->get();
  }

  public function getSiblingsOfStudent($niy)
  {
    $student = $this->studentRepository->getStudentByNIY($niy)->first();
    return $this->studentRepository->getSiblingByParentsId($niy, $student->father_parent_id, $student->mother_parent_id)->get();
  }

  public function exportStudent($filters, $type)
  {
    if ($type === 'pdf') {
    } elseif ($type === 'xls' || $type === 'xlsx') {

      if (isset($filters['is_sibling'])) {
        $studentWithSiblings = $this->studentRepository->getStudentsByFilters($filters)->get();
        $students = $this->mergeSiblings($studentWithSiblings);
      } else {
        $students = $this->studentRepository->getStudentsByFilters($filters)->get();
      }

      // dd($students);
      return Excel::download(new StudentExcel($students), 'student.' . $type);
    }
  }

  public function createStudent($data)
  {
    $this->studentRepository->insertStudent($data);
  }

  public function updateStudent($data, $id)
  {
    $this->studentRepository->updateStudent($data, $id);
  }

  public function saveStudentMutation()
  {
    $students = $this->studentRepository->getStudentsByJenjang($this->jenjang->id)->get();
    foreach ($students as $student) {
      $dataMutation = [
        'jenjang_id' => $student->jenjang_id,
        'school_id' => $student->school_id,
        'kelas_id' => $student->kelas_id,
        'parallel_id' => $student->parallel_id,
        'tahun_pelajaran_id' => $this->tahunActive->id,
        'nis' => $student->nis,
        'niy' => $student->niy,
        'nkk' => $student->nkk,
        'photo'  => $student->photo,
        'is_active'  => $student->is_active
      ];

      $studentMutation = $this->studentRepository->getStudentMutationNiyYear($student->niy, $this->tahunActive->id)->first();
      if ($studentMutation) {
        $this->studentRepository->updateStudentMutation($dataMutation, $studentMutation->id);
      } else {
        $this->studentRepository->insertStudentMutation($dataMutation);
      }
    }
  }

  public function syncStudentTK($tahun_pelajaran_id)
  {

    set_time_limit(0);
    $father_parent_id = null;
    $mother_parent_id = null;
    $this->jenjang = $this->jenjangRepository->getJenjangByCode('TK')->first();
    $this->school = $this->schoolRepository->getSchoolByJenjang($this->jenjang->id)->first();
    if (isset($tahun_pelajaran_id)) {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranById($tahun_pelajaran_id)->first();
    } else {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();
    }
    $roleStudent = $this->roleRepository->getRoleByCode('STUDENT')->first();
    $roleParent = $this->roleRepository->getRoleByCode('PARENT')->first();
    $existingStudents = $this->studentRepository->getAllStudents()->pluck('niy')->all();
    $existingUsers = $this->userRepository->getAllUsers()->pluck('username')->all();
    $studentTKs = $this->studentRepository->getStudentWithBukuIndukTK($this->tahunActive->name)->get();

    //F81204004

    $siblingIds = [];
    // dd($studentTKs);
    foreach ($studentTKs as $studentTK) {
      $existingStudents = $this->studentRepository->getAllStudents()->pluck('niy')->all();
      $dataStudent = $this->mapStudentTKToStudent($studentTK);
      $kkAyah = strlen($studentTK->bukuInduk->no_kartu_keluarga) < 16 ? null : $studentTK->bukuInduk->no_kartu_keluarga;
      $ktpAyah = strlen($studentTK->bukuInduk->no_ktp_ayah) < 16 ? null : $studentTK->bukuInduk->no_ktp_ayah;
      $emailAyah = $studentTK->bukuInduk->email_ayah === "" || $studentTK->bukuInduk->email_ayah === "-" ? null : $studentTK->bukuInduk->email_ayah;
      $mobilePhoneAyah = strlen($studentTK->bukuInduk->hp_ayah) < 5 ? null : $studentTK->bukuInduk->hp_ayah;

      $kkIbu = strlen($studentTK->bukuInduk->no_kartu_keluarga) < 16 ? null : $studentTK->bukuInduk->no_kartu_keluarga;
      $ktpIbu = strlen($studentTK->bukuInduk->no_ktp_ibu) < 16 ? null : $studentTK->bukuInduk->no_ktp_ibu;
      $emailIbu = $studentTK->bukuInduk->email_ibu === "" || $studentTK->bukuInduk->email_ibu === "-" ? null : $studentTK->bukuInduk->email_ibu;
      $mobilePhoneIbu = strlen($studentTK->bukuInduk->hp_ibu) < 5 ? null : $studentTK->bukuInduk->hp_ibu;

      //$existingFather = $this->parentRepository->getExistingParentByIdentity($ktpAyah, $kkAyah,1)->first();

      $existingFather = null;
      // dd($ktpIbu);
      if ($ktpAyah !== null) {
        $existingFather = $this->parentRepository->getExistingParentByKtp($ktpAyah, 1)->first();
      }
      if ($existingFather === null && $kkAyah !== null) {

        $existingFather = $this->parentRepository->getExistingParentByKk($kkAyah, 1)->first();
      }
      if ($existingFather === null && $mobilePhoneAyah !== null) {
        $existingFather = $this->parentRepository->getExistingParentByMobilePhone($mobilePhoneAyah, 1)->first();
      }


      $existingMother = null;

      if ($ktpIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByKtp($ktpIbu, 2)->first();
      }
      if ($existingMother === null && $kkIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByKk($kkIbu, 2)->first();
      }
      if ($existingMother === null && $mobilePhoneIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByMobilePhone($mobilePhoneIbu, 2)->first();
      }




      //father
      // dd($existingMother);
      // if (!in_array($studentTK->nis, $existingStudents)) {
      if ($existingFather) {
        $father_parent_id = $existingFather->id;
        $resultResultIds = $this->studentRepository->getSiblingByFatherId($studentTK->nis, $father_parent_id)->pluck('id');


        if (isset($resultResultIds[0])) {
          // dd(isset($resultResultIds[0]));
          $siblingIds = array_merge($siblingIds, $resultResultIds->all());
        }
      } else {
        if (!in_array('F' . $studentTK->nis, $existingUsers)) {
          // dd('F' . $studentTK->nis);
          $user_father_id = $this->userRepository->insertUserGetId(
            [
              'status_active' => 99,
              'name' => $studentTK->bukuInduk->nama_ayah,
              'email' => $emailAyah,
              'username' => 'F' . $studentTK->nis,
              'password' => bcrypt('F' . $studentTK->nis),
              'user_type_value' => 3
            ]
          );

          $dataFather = $this->mapStudentTKToFather($studentTK);
          $dataFather['user_id'] = $user_father_id;
          $dataFather['ktp'] = $ktpAyah;
          $dataFather['nkk'] =  $kkAyah;
          $dataFather['email'] = $emailAyah;
          $dataFather['mobilePhone'] = $mobilePhoneAyah;


          $father_parent_id = $this->parentRepository->insertParentGetId($dataFather);
          $this->roleRepository->syncUserRole($user_father_id, $roleParent->id);
          array_push($existingUsers, 'F' . $studentTK->nis);
        } else {
          $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentTK->nis)->first();
          $existingFatherInParent = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->pluck('id')->all();
          if ($existingFatherInParent == null) {
            $dataFather = $this->mapStudentTKToFather($studentTK);
            $dataFather['user_id'] = $fatherIdInUser->id;
            $dataFather['ktp'] = $ktpAyah;
            $dataFather['nkk'] =  $kkAyah;
            $dataFather['email'] = $emailAyah;
            $dataFather['mobilePhone'] = $mobilePhoneAyah;


            $father_parent_id = $this->parentRepository->insertParentGetId($dataFather);
            // dd($father_parent_id);
            $this->roleRepository->syncUserRole($fatherIdInUser->id, $roleParent->id);
          }
        }
      }
      // }


      //mother
      // dd($mobilePhoneIbu);
      // if (!in_array($studentTK->nis, $existingStudents)) {
      if ($existingMother) {
        $mother_parent_id = $existingMother->id;
        $resultResultIds = $this->studentRepository->getSiblingByMotherId($studentTK->nis, $mother_parent_id)->pluck('id');
        if (isset($resultResultIds[0])) {
          $siblingIds = array_merge($siblingIds, $resultResultIds->all());
        }
      } else {
        if (!in_array('M' . $studentTK->nis, $existingUsers)) {
          $user_mother_id = $this->userRepository->insertUserGetId(
            [
              'status_active' => 99,
              'name' => $studentTK->bukuInduk->nama_ibu,
              'email' => $emailIbu,
              'username' => 'M' . $studentTK->nis,
              'password' => bcrypt('M' . $studentTK->nis),
              'user_type_value' => 3
            ]
          );

          $dataMother = $this->mapStudentTKToMother($studentTK);
          $dataMother['user_id'] = $user_mother_id;
          $dataMother['ktp'] = $ktpIbu;
          $dataMother['nkk'] =  $kkIbu;
          $dataMother['email'] = $emailIbu;
          $dataMother['mobilePhone'] = $mobilePhoneIbu;

          $mother_parent_id = $this->parentRepository->insertParentGetId($dataMother);
          $this->roleRepository->syncUserRole($user_mother_id, $roleParent->id);
          array_push($existingUsers, 'M' . $studentTK->nis);
        } else {
          $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentTK->nis)->first();
          $existingMotherInParent = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->pluck('id')->all();

          if ($existingMotherInParent == null) {
            $dataMother = $this->mapStudentTKToMother($studentTK);
            $dataMother['user_id'] = $motherIdInUser->id;
            $dataMother['ktp'] = $ktpIbu;
            $dataMother['nkk'] =  $kkIbu;
            $dataMother['email'] = $emailIbu;
            $dataMother['mobilePhone'] = $mobilePhoneIbu;
            // dd($dataMother);


            $mother_parent_id = $this->parentRepository->insertParentGetId($dataMother);
            $this->roleRepository->syncUserRole($motherIdInUser->id, $roleParent->id);
          }
        }
      }
      // }


      //student
      if (!in_array($studentTK->nis, $existingUsers)) {
        $user_student_id = $this->userRepository->insertUserGetId(
          [
            'status_active' => 99,
            'name' => $studentTK->nama,
            'username' => $studentTK->nis,
            'password' => bcrypt($studentTK->nis),
            'user_type_value' => 2
          ]
        );


        $dataStudent['user_id'] = $user_student_id;
        $dataStudent['father_ktp'] = $ktpAyah;
        $dataStudent['mother_ktp'] = $ktpIbu;
        $dataStudent['father_parent_id'] = $father_parent_id;
        $dataStudent['mother_parent_id'] = $mother_parent_id;
        $dataStudent['jenjang_id'] = $this->jenjang->id;
        $dataStudent['school_id'] = $this->school->id;


        $this->studentRepository->insertStudent($dataStudent);
        $this->roleRepository->syncUserRole($user_student_id, $roleStudent->id);
        array_push($existingUsers, $studentTK->nis);
      } else {
        if (!in_array($studentTK->nis, $existingStudents)) {
          $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentTK->nis)->first();
          $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentTK->nis)->first();
          if ($motherIdInUser) {
            $motherID = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->first();
          } else {
            $motherID = null;
          }

          if ($fatherIdInUser) {
            $fatherID = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->first();
          } else {
            $fatherID = null;
          }

          $studentInUser = $this->userRepository->getUserByUsername($studentTK->nis)->first();
          $dataStudent['user_id'] = $studentInUser->id;
          $dataStudent['father_ktp'] = $ktpAyah;
          $dataStudent['mother_ktp'] = $ktpIbu;
          $dataStudent['father_parent_id'] = $fatherID ? $fatherID->id : null;
          $dataStudent['mother_parent_id'] = $motherID ? $motherID->id : null;
          $dataStudent['jenjang_id'] = $this->jenjang->id;
          $dataStudent['school_id'] = $this->school->id;

          $this->studentRepository->insertStudent($dataStudent);
          $this->roleRepository->syncUserRole($studentInUser->id, $roleStudent->id);
        } else {
          $this->studentRepository->updateStudentByNIY($dataStudent, $studentTK->nis);
        }
      }
    }
    //array_push($test,$this->mapStudentTKToFather($studentTK));          

    //return $test;

    $this->studentRepository->updateStudents(['is_sibling_student' => 1], $siblingIds);
    $this->saveStudentMutation();


    return response([
      'success' => true,
      'message' => 'success',
    ], 200);
  }


  public function mapStudentTKToStudent($studentTK)
  {
    dd($studentTK->toArray());
    $kelas = $this->kelasRepository->getKelasByNameAndJenjangId(trim($studentTK->level), $this->jenjang->id)->first();
    $parallel = null;


    if ($kelas) {
      $parallel = $this->parallelRepository->getParallelByNameAndKelasId(trim($studentTK->kelas), $kelas->id)->first();
    }

    $kk = null;
    if ($studentTK->bukuInduk->nomor_kartu_keluarga != null) {
      $kk = $studentTK->bukuInduk->nomor_kartu_keluarga;
    } else if ($studentTK->bukuInduk->kkSiswa != null) {
      $kk = $studentTK->bukuInduk->kkSiswa;
    }

    return [
      'kelas_id' => $kelas ? $kelas->id : null,
      'parallel_id' => $parallel ? $parallel->id : null,
      'name' => $studentTK->nama,
      'nis' => $studentTK->nis,
      'niy' => $studentTK->nis,
      'nkk' => $kk,
      'sex_type_value'  => $studentTK->bukuInduk->jen_kel === 'Perempuan' ? 2 : 1,
      'address'  => $studentTK->bukuInduk->alamat,
      'kodepos'  => $studentTK->bukuInduk->kodepos,
      'birth_place'  => $studentTK->tmp_lahir,
      'birth_date'  => $studentTK->tgl_lahir,
      'birth_order'  => $studentTK->bukuInduk->anak_keberapa,
      'nationality'  => $studentTK->bukuInduk->warga_negara,
      'is_active'  => $studentTK->active ?  $studentTK->active : 0,
      //'masuk_tahun_id'	=> null,
      //'masuk_jenjang_id'	=> null,
      //'masuk_kelas_id'	=> null,
      //'is_father_alive'	=> 1,
      //'is_mother_alive'	=> 1,
      //'is_poor'	=> 0,
      //'nisn'	=> $studentSD->coverRapotSD->nisn,
      //'email'	=> null,
      //'kota'	=> null,
      //'kecamatan'	=> null,
      //'kelurahan'	=> null,
      //'photo'	=> null,
      //'handphone'	=> null,
      //'religion_value'	=> 0,
      //'language'	=> null,
      //'is_adopted'	=> 0,
      //'stay_with_value'	=> 1,
      //'siblings'	=> $studentSD->studentSiswaSD->jumlahSaudara,
      //'is_sibling_student'	=> 0,
      //'foster'	=> 0,
      //'step_siblings'	=> 0,
      //'medical_history'	=> null,
      //'student_status_value'	=> 1,
      //'lulus_tahun_id'	=> null,
      //'tahun_lulus'	=> null,
      //'gol_darah'	=> null,
      'is_cacat'  => 0,
      //'tinggi'	=> 0,
      //'berat'	=> 0,
      //'sekolah_asal'	=> $studentSD->studentSiswaSD->sekolahAsal
    ];
  }

  public function mapStudentTKToFather($studentTK)
  {
    return [
      'name' => $studentTK->bukuInduk->nama_ayah,
      'birth_date' => '1890-01-01',
      'sex_type_value' => 1,
      'parent_type_value' => 1,
      'wali_type_value' => null,
      'job' => $studentTK->bukuInduk->pekerjaan_ayah,
      //'jobCorporateName'=> '',
      //'jobPositionName'=> '',
      //'jobWorkAddress'=> ''     
    ];
  }

  public function mapStudentTKToMother($studentTK)
  {
    return [
      'name' => $studentTK->bukuInduk->nama_ibu,
      'birth_date' => '1890-01-01',
      'sex_type_value' => 2,
      'parent_type_value' => 2,
      'wali_type_value' => null,
      'job' => $studentTK->bukuInduk->pekerjaan_ibu,
      //'jobCorporateName'=> '',
      //'jobPositionName'=> '',
      //'jobWorkAddress'=> '' 
    ];
  }

  public function syncStudentSD($tahun_pelajaran_id)
  {
    set_time_limit(0);
    $father_parent_id = null;
    $mother_parent_id = null;
    $this->jenjang = $this->jenjangRepository->getJenjangByCode('SD')->first();
    $this->school = $this->schoolRepository->getSchoolByJenjang($this->jenjang->id)->first();
    if (isset($tahun_pelajaran_id)) {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranById($tahun_pelajaran_id)->first();
    } else {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();
    }
    $roleStudent = $this->roleRepository->getRoleByCode('STUDENT')->first();
    $roleParent = $this->roleRepository->getRoleByCode('PARENT')->first();
    $existingStudents = $this->studentRepository->getAllStudents()->pluck('niy')->all();
    $existingUsers = $this->userRepository->getAllUsers()->pluck('username')->all();
    $studentSDs = $this->studentRepository
      ->getStudentSDAndKelasWithBukuInduk($this->tahunActive->name)->get();
    // dd($studentSDs);

    $studentKelasSDs = $this->studentRepository->getStudentKelasSD($this->tahunActive->name)->get();



    $test = [];
    $siblingIds = [];

    foreach ($studentSDs as $studentSD) {
      // dd($studentSD);
      $dataStudent = $this->mapStudentSDToStudent($studentSD);
      $kkAyah = strlen($studentSD->studentSD->kkAyah) < 15 ? null : $studentSD->studentSD->kkAyah;
      $ktpAyah =  strlen($studentSD->studentSD->ktpAyah) < 15 ? null : $studentSD->studentSD->ktpAyah;
      $emailAyah = $studentSD->studentSD->email === "" ? null : $studentSD->studentSD->email;
      $mobilePhoneAyah = strlen($studentSD->studentSD->hp) < 5 ? null : $studentSD->studentSD->hp;


      $kkIbu = strlen($studentSD->studentSD->kkIbu) < 15 ? null : $studentSD->studentSD->kkIbu;
      $ktpIbu = strlen($studentSD->ktpIbu) < 15 ? null : $studentSD->studentSD->ktpIbu;
      $emailIbu = $studentSD->studentSD->email === "" ? null : $studentSD->studentSD->email;
      $mobilePhoneIbu = strlen($studentSD->studentSD->hpibu) < 5 ? null : $studentSD->studentSD->hpibu;

      $existingFather = null;

      if ($ktpAyah !== null) {
        $existingFather = $this->parentRepository->getExistingParentByKtp($ktpAyah, 1)->first();
      }
      if ($existingFather === null && $kkAyah !== null) {
        $existingFather = $this->parentRepository->getExistingParentByKk($kkAyah, 1)->first();
      }
      if ($existingFather === null && $mobilePhoneAyah !== null) {
        $existingFather = $this->parentRepository->getExistingParentByMobilePhone($mobilePhoneAyah, 1)->first();
      }



      $existingMother = null;

      if ($ktpIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByKtp($ktpIbu, 2)->first();
      }
      if ($existingMother === null && $kkIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByKk($kkIbu, 2)->first();
      }
      if ($existingMother === null && $mobilePhoneIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByMobilePhone($mobilePhoneIbu, 2)->first();
      }



      //father
      if ($existingFather) {

        $father_parent_id = $existingFather->id;
        $resultResultIds = $this->studentRepository->getSiblingByFatherId($studentSD->no_induk_pahoa, $father_parent_id)->pluck('id');

        if (isset($resultResultIds[0])) {
          $siblingIds = array_merge($siblingIds, $resultResultIds->all());
        }
      } else {
        if (!in_array('F' . $studentSD->no_induk_pahoa, $existingUsers)) {
          $user_father_id = $this->userRepository->insertUserGetId(
            [
              'status_active' => 99,
              'name' => $studentSD->studentSD->namaAyah,
              'email' => $emailAyah,
              'username' => 'F' . $studentSD->no_induk_pahoa,
              'password' => bcrypt('F' . $studentSD->no_induk_pahoa),
              'user_type_value' => 3
            ]
          );
          $dataFather = $this->mapStudentSDToFather($studentSD);
          $dataFather['user_id'] = $user_father_id;
          $dataFather['ktp'] = $ktpAyah;
          $dataFather['nkk'] =  $kkAyah;
          $dataFather['email'] = $emailAyah;
          $dataFather['mobilePhone'] = $mobilePhoneAyah;

          $father_parent_id = $this->parentRepository->insertParentGetId($dataFather);
          $this->roleRepository->syncUserRole($user_father_id, $roleParent->id);
          array_push($existingUsers, 'F' . $studentSD->no_induk_pahoa);
        } else {
          $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentSD->no_induk_pahoa)->first();
          $existingFatherInParent = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->pluck('id')->all();
          // dd($existingFatherInParent);
          if ($existingFatherInParent == null) {
            $dataFather = $this->mapStudentSDToFather($studentSD);
            $dataFather['user_id'] = $fatherIdInUser->id;
            $dataFather['ktp'] = $ktpAyah;
            $dataFather['nkk'] =  $kkAyah;
            $dataFather['email'] = $emailAyah;
            $dataFather['mobilePhone'] = $mobilePhoneAyah;


            $father_parent_id = $this->parentRepository->insertParentGetId($dataFather);
            // dd($father_parent_id);
            $this->roleRepository->syncUserRole($fatherIdInUser->id, $roleParent->id);
          }
        }
      }

      //mother
      if ($existingMother) {
        $mother_parent_id = $existingMother->id;
        $resultResultIds = $this->studentRepository->getSiblingByMotherId($studentSD->no_induk_pahoa, $mother_parent_id)->pluck('id');
        if (isset($resultResultIds[0])) {
          $siblingIds = array_merge($siblingIds, $resultResultIds->all());
        }
      } else {
        if (!in_array('M' . $studentSD->no_induk_pahoa, $existingUsers)) {
          $user_mother_id = $this->userRepository->insertUserGetId(
            [
              'status_active' => 99,
              'name' => $studentSD->studentSD->namaIbu,
              'email' => $emailIbu,
              'username' => 'M' . $studentSD->no_induk_pahoa,
              'password' => bcrypt('M' . $studentSD->no_induk_pahoa),
              'user_type_value' => 3
            ]
          );

          $dataMother = $this->mapStudentSDToMother($studentSD);
          $dataMother['user_id'] = $user_mother_id;
          $dataMother['ktp'] = $ktpIbu;
          $dataMother['nkk'] =  $kkIbu;
          $dataMother['email'] = $emailIbu;
          $dataMother['mobilePhone'] = $mobilePhoneIbu;

          $mother_parent_id = $this->parentRepository->insertParentGetId($dataMother);
          $this->roleRepository->syncUserRole($user_mother_id, $roleParent->id);
          array_push($existingUsers, 'M' . $studentSD->no_induk_pahoa);
        } else {
          $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentSD->no_induk_pahoa)->first();
          $existingMotherInParent = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->pluck('id')->all();
          if ($existingMotherInParent == null) {
            // dd($motherIdInUser);
            $dataMother = $this->mapStudentSDToMother($studentSD);
            $dataMother['user_id'] = $motherIdInUser->id;
            $dataMother['ktp'] = $ktpIbu;
            $dataMother['nkk'] =  $kkIbu;
            $dataMother['email'] = $emailIbu;
            $dataMother['mobilePhone'] = $mobilePhoneIbu;


            $mother_parent_id = $this->parentRepository->insertParentGetId($dataMother);
            // dd($father_parent_id);
            $this->roleRepository->syncUserRole($motherIdInUser->id, $roleParent->id);
          }
        }
      }


      //student
      if (!in_array($studentSD->no_induk_pahoa, $existingUsers)) {
        // dd($studentSD->no_induk_pahoa);
        $user_student_id = $this->userRepository->insertUserGetId(
          [
            'status_active' => 99,
            'name' => $studentSD->nama,
            'username' => $studentSD->no_induk_pahoa,
            'password' => bcrypt($studentSD->no_induk_pahoa),
            'user_type_value' => 2
          ]
        );


        $dataStudent['user_id'] = $user_student_id;
        $dataStudent['father_ktp'] = $ktpAyah;
        $dataStudent['mother_ktp'] = $ktpIbu;
        $dataStudent['father_parent_id'] = $father_parent_id;
        $dataStudent['mother_parent_id'] = $mother_parent_id;
        $dataStudent['jenjang_id'] = $this->jenjang->id;
        $dataStudent['school_id'] = $this->school->id;

        $this->studentRepository->insertStudent($dataStudent);
        $this->roleRepository->syncUserRole($user_student_id, $roleStudent->id);
        array_push($existingUsers, $studentSD->no_induk_pahoa);
      } else {
        // dd($existingStudents);
        if (!in_array($studentSD->no_induk_pahoa, $existingStudents)) {
          $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentSD->no_induk_pahoa)->first();
          $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentSD->no_induk_pahoa)->first();
          if ($motherIdInUser) {
            $motherID = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->first();
          } else {
            $motherID = null;
          }

          if ($fatherIdInUser) {
            $fatherID = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->first();
          } else {
            $fatherID = null;
          }

          $existingUsers = $this->userRepository->getAllUsers()->pluck('username')->all();
          $studentInUser = $this->userRepository->getUserByUsername($studentSD->no_induk_pahoa)->first();
          $dataStudent['user_id'] = $studentInUser->id;
          $dataStudent['father_ktp'] = $ktpAyah;
          $dataStudent['mother_ktp'] = $ktpIbu;
          $dataStudent['father_parent_id'] = $fatherID ? $fatherID->id : null;
          $dataStudent['mother_parent_id'] = $motherID ? $motherID->id : null;
          $dataStudent['jenjang_id'] = $this->jenjang->id;
          $dataStudent['school_id'] = $this->school->id;

          $this->studentRepository->insertStudent($dataStudent);
          array_push($existingStudents, $studentSD->no_induk_pahoa);
        } else {
          $this->studentRepository->updateStudentByNIY($dataStudent, $studentSD->no_induk_pahoa);
        }
      }
    }

    //return $test;


    $this->studentRepository->updateStudents(['is_sibling_student' => 1], $siblingIds);
    $this->saveStudentMutation();



    return ['message' => 'success'];
  }

  public function mapStudentSDToStudent($studentSD)
  {
    if ($studentSD->studentSD == null) {
      $anakKe = 0;
      $saudara = 0;
    } else {
      $anakKe = $studentSD->studentSD->anakKe;
      $saudara = $studentSD->studentSD->jumlahSaudara;
    }

    $kelas_id = $this->kelasRepository->getKelasByNameAndJenjangId($studentSD->kelasSD->kelas, $this->jenjang->id)->pluck('id')->first();
    $parallel_id = $this->parallelRepository->getParallelByNameAndJenjangId($studentSD->kelasSD->paralel, $this->jenjang->id)->pluck('id')->first();
    // dd($parallel_id);
    $jenisKelamin = strtolower($studentSD->jenisKelamin);
    if ($studentSD->studentSD == "" || $studentSD->studentSD == null) {
      $bulanLahir = "September";
    } else {
      $bulanLahir = $studentSD->studentSD->blnLahir;
    }
    if ($studentSD->studentSD == "" || $studentSD->studentSD == null) {
      $tanggalLahir = 9;
    } else {
      $tanggalLahir = $studentSD->studentSD->tglLahir;
    }
    if ($studentSD->studentSD == "" || $studentSD->studentSD == null) {
      $tahunLahir = 9999;
    } else {
      $tahunLahir = $studentSD->studentSD->thnLahir;
    }
    if ($studentSD->coverKelas == null) {
      $nisn = null;
    } else {
      $nisn = $studentSD->coverKelas->nisn;
    }
    // dd($studentSD->coverKelas->nisn);
    return [
      'kelas_id' => $kelas_id,
      'parallel_id' => $parallel_id,
      //'masuk_tahun_id'	=> null,
      //'masuk_jenjang_id'	=> null,
      //'masuk_kelas_id'	=> null,
      //'is_father_alive'	=> 1,
      //'is_mother_alive'	=> 1,
      //'is_poor'	=> 0,
      'name' => $studentSD->nama,
      'nis' => $studentSD->nis,
      'niy' => $studentSD->no_induk_pahoa,
      'nisn'  => $nisn,
      'nkk' => $studentSD->studentSD->kkSiswa,
      //'father_ktp'	=> $studentSD->studentSiswaSD->ktpAyah,
      //'mother_ktp'	=> $studentSD->studentSiswaSD->ktpIbu,
      //'email'	=> null,
      'sex_type_value'  => $jenisKelamin === "laki-laki" ? 1 : 2,
      'address'  => $studentSD->studentSD->alamat,
      //'kota'	=> null,
      //'kecamatan'	=> null,
      //'kelurahan'	=> null,
      'kodepos'  => $studentSD->studentSD->kodepos,
      //'photo'	=> null,
      //'handphone'	=> null,
      'birth_place'  => $studentSD->studentSD->tmpLahir,
      'birth_date'  => $tahunLahir . "-" . $this->monthNames[$bulanLahir] . "-" . $tanggalLahir,
      'birth_order'  => $anakKe,
      //'religion_value'	=> 0,
      'nationality'  => $studentSD->studentSD->warganegara,
      //'language'	=> null,
      //'is_adopted'	=> 0,
      //'stay_with_value'	=> 1,
      'siblings'  => $saudara,
      //'is_sibling_student'	=> 0,
      //'foster'	=> 0,
      //'step_siblings'	=> 0,
      //'medical_history'	=> null,
      'is_active'  => 1,
      'student_status_value'  => 1,
      //'lulus_tahun_id'	=> null,
      //'tahun_lulus'	=> null,
      //'gol_darah'	=> null,
      'is_cacat'  => 0,
      //'tinggi'	=> 0,
      //'berat'	=> 0,
      'sekolah_asal'  => $studentSD->studentSD->sekolahAsal
    ];
  }

  public function mapStudentSDToFather($studentSD)
  {
    if ($studentSD->studentSD == null) {
      $nama = "unknown";
    } else {
      $nama = $studentSD->studentSD->namaAyah;
    }
    return [
      'name' => $nama,
      //'birth_date'=> '1890-01-01',
      'sex_type_value' => 1,
      'parent_type_value' => 1,
      //'wali_type_value'=> null,
      'job' => $studentSD->studentSD->pekerjaanAyah,
      //'jobCorporateName'=> '',
      //'jobPositionName'=> '',
      //'jobWorkAddress'=> '',
      //'ktp'=> $studentSD->ktpAyah,
      //'nkk'=> $studentSD->kkAyah,
      //'email'=> '',
      //'mobilePhone'=> ''
    ];
  }

  public function mapStudentSDToMother($studentSD)
  {
    if ($studentSD->studentSD == null) {
      $nama = "unknown";
    } else {
      $nama = $studentSD->studentSD->namaIbu;
    }
    return [
      'name' => $nama,
      //'birth_date'=> '1890-01-01',
      'sex_type_value' => 2,
      'parent_type_value' => 2,
      //'wali_type_value'=> null,
      'job' => $studentSD->studentSD->pekerjaanIbu,
      //'jobCorporateName'=> '',
      //'jobPositionName'=> '',
      //'jobWorkAddress'=> ''
      //'ktp'=> $studentSD->studentSiswaSD->ktpIbu,
      //'nkk'=> $studentSD->studentSiswaSD->kkIbu,
      //'email'=> '',
      //'mobilePhone'=> ''
    ];
  }

  public function syncStudentSMP($tahun_pelajaran_id)
  {
    set_time_limit(0);
    $father_parent_id = null;
    $mother_parent_id = null;
    $this->jenjang = $this->jenjangRepository->getJenjangByCode('SMP')->first();
    $this->school = $this->schoolRepository->getSchoolByJenjang($this->jenjang->id)->first();
    if (isset($tahun_pelajaran_id)) {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranById($tahun_pelajaran_id)->first();
    } else {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();
    }
    $roleStudent = $this->roleRepository->getRoleByCode('STUDENT')->first();
    $roleParent = $this->roleRepository->getRoleByCode('PARENT')->first();
    $existingStudents = $this->studentRepository->getAllStudents()->pluck('niy')->all();

    $existingUsers = $this->userRepository->getAllUsers()->pluck('username')->all();
    $studentSMPs = $this->studentRepository->getStudentSMPWithBukuInduk($this->tahunActive->name)->get();

    $test = [];

    $siblingIds = [];



    foreach ($studentSMPs as $studentSMP) {
      // dd($studentSMP);

      $dataStudent = $this->mapStudentSMPToStudent($studentSMP);

      $emailAyah = null;
      $kkAyah = strlen($studentSMP->siswaEdit->kkAyah) < 16 ? null : $studentSMP->siswaEdit->kkAyah;
      $ktpAyah = strlen($studentSMP->siswaEdit->ktpAyah) < 16 ? null : $studentSMP->siswaEdit->ktpAyah;
      //$emailAyah = $studentSMP->studentSiswaSD->email===""? null : $studentSMP->studentSiswaSD->email;
      $mobilePhoneAyah = strlen($studentSMP->hpAyah) < 5 ? null : $studentSMP->hpAyah;


      $emailIbu = null;
      $kkIbu = strlen($studentSMP->siswaEdit->kkIbu) < 16 ? null : $studentSMP->siswaEdit->kkIbu;
      $ktpIbu = strlen($studentSMP->siswaEdit->ktpIbu) < 16 ? null : $studentSMP->siswaEdit->ktpIbu;
      //$emailIbu = $studentSMP->studentSiswaSD->email===""? null : $studentSMP->studentSiswaSD->email;
      $mobilePhoneIbu = strlen($studentSMP->siswaEdit->hpIbu) < 5 ? null : $studentSMP->siswaEdit->hpIbu;

      $existingFather = null;
      if ($ktpAyah !== null) {
        $existingFather = $this->parentRepository->getExistingParentByKtp($ktpAyah, 1)->first();
      }
      if ($existingFather === null && $kkAyah !== null) {
        $existingFather = $this->parentRepository->getExistingParentByKk($kkAyah, 1)->first();
      }
      if ($existingFather === null && $mobilePhoneAyah !== null) {
        $existingFather = $this->parentRepository->getExistingParentByMobilePhone($mobilePhoneAyah, 1)->first();
      }


      $existingMother = null;

      if ($ktpIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByKtp($ktpIbu, 2)->first();
      }
      if ($existingMother === null && $kkIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByKk($kkIbu, 2)->first();
      }
      if ($existingMother === null && $mobilePhoneIbu !== null) {
        $existingMother = $this->parentRepository->getExistingParentByMobilePhone($mobilePhoneIbu, 2)->first();
      }


      if ($studentSMP->siswaEdit->nama_ayah) {
        if ($existingFather) {
          $father_parent_id = $existingFather->id;
          $resultResultIds = $this->studentRepository->getSiblingByFatherId($studentSMP->nis, $father_parent_id)->pluck('id');
          if (isset($resultResultIds[0])) {
            $siblingIds = array_merge($siblingIds, $resultResultIds->all());
          }
        } else {
          if (!in_array('F' . $studentSMP->nis, $existingUsers)) {
            $user_father_id = $this->userRepository->insertUserGetId(
              [
                'status_active' => 99,
                'name' => $studentSMP->siswaEdit->nama_ayah,
                //'email'=>$emailAyah,
                'username' => 'F' . $studentSMP->nis,
                'password' => bcrypt('F' . $studentSMP->nis),
                'user_type_value' => 3
              ]
            );
            $dataFather = $this->mapStudentSMPToFather($studentSMP);
            $dataFather['user_id'] = $user_father_id;
            $dataFather['ktp'] = $ktpAyah;
            $dataFather['nkk'] =  $kkAyah;
            $dataFather['email'] = $emailAyah;
            $dataFather['mobilePhone'] = $mobilePhoneAyah;

            $father_parent_id = $this->parentRepository->insertParentGetId($dataFather);
            $this->roleRepository->syncUserRole($user_father_id, $roleParent->id);
            array_push($existingUsers, 'F' . $studentSMP->nis);
          } else {
            $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentSMP->nis)->first();
            $existingFatherInParent = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->pluck('id')->all();
            if ($existingFatherInParent == null) {
              $dataFather = $this->mapStudentSMPToFather($studentSMP);
              $dataFather['user_id'] = $fatherIdInUser->id;
              $dataFather['ktp'] = $ktpAyah;
              $dataFather['nkk'] =  $kkAyah;
              $dataFather['email'] = $emailAyah;
              $dataFather['mobilePhone'] = $mobilePhoneAyah;


              $father_parent_id = $this->parentRepository->insertParentGetId($dataFather);
              // dd($father_parent_id);
              $this->roleRepository->syncUserRole($fatherIdInUser->id, $roleParent->id);
            }
          }
        }
      }


      //mother
      if ($studentSMP->siswaEdit->nama_ibu) {
        if ($existingMother) {
          $mother_parent_id = $existingMother->id;
          $resultResultIds = $this->studentRepository->getSiblingByMotherId($studentSMP->nis, $mother_parent_id)->pluck('id');
          if (isset($resultResultIds[0])) {
            $siblingIds = array_merge($siblingIds, $resultResultIds->all());
          }
        } else {
          if (!in_array('M' . $studentSMP->nis, $existingUsers)) {
            $user_mother_id = $this->userRepository->insertUserGetId(
              [
                'status_active' => 99,
                'name' => $studentSMP->siswaEdit->nama_ibu,
                //'email'=>$emailIbu,
                'username' => 'M' . $studentSMP->nis,
                'password' => bcrypt('M' . $studentSMP->nis),
                'user_type_value' => 3
              ]
            );

            $dataMother = $this->mapStudentSMPToMother($studentSMP);
            $dataMother['user_id'] = $user_mother_id;
            $dataMother['ktp'] = $ktpIbu;
            $dataMother['nkk'] =  $kkIbu;
            $dataMother['email'] = $emailIbu;
            $dataMother['mobilePhone'] = $mobilePhoneIbu;

            $mother_parent_id = $this->parentRepository->insertParentGetId($dataMother);
            $this->roleRepository->syncUserRole($user_mother_id, $roleParent->id);
            array_push($existingUsers, 'M' . $studentSMP->nis);
          } else {
            $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentSMP->nis)->first();
            $existingMotherInParent = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->pluck('id')->all();
            if ($existingMotherInParent == null) {
              // dd($motherIdInUser);
              $dataMother = $this->mapStudentSMPToMother($studentSMP);
              $dataMother['user_id'] = $motherIdInUser->id;
              $dataMother['ktp'] = $ktpIbu;
              $dataMother['nkk'] =  $kkIbu;
              $dataMother['email'] = $emailIbu;
              $dataMother['mobilePhone'] = $mobilePhoneIbu;


              $mother_parent_id = $this->parentRepository->insertParentGetId($dataMother);
              // dd($father_parent_id);
              $this->roleRepository->syncUserRole($motherIdInUser->id, $roleParent->id);
            }
          }
        }
      }

      //student
      if (!in_array($studentSMP->nis, $existingUsers)) {
        $user_student_id = $this->userRepository->insertUserGetId(
          [
            'status_active' => 99,
            'name' => $studentSMP->studentSMP->nama,
            //'email'=>$studentSMP->nis."@email.com",
            'username' => $studentSMP->nis,
            'password' => bcrypt($studentSMP->nis),
            'user_type_value' => 2
          ]
        );



        $dataStudent['user_id'] = $user_student_id;
        $dataStudent['father_ktp'] = $ktpAyah;
        $dataStudent['mother_ktp'] = $ktpIbu;
        $dataStudent['father_parent_id'] = $father_parent_id;
        $dataStudent['mother_parent_id'] = $mother_parent_id;
        $dataStudent['jenjang_id'] = $this->jenjang->id;
        $dataStudent['school_id'] = $this->school->id;


        $this->studentRepository->insertStudent($dataStudent);
        $this->roleRepository->syncUserRole($user_student_id, $roleStudent->id);
        array_push($existingUsers, $studentSMP->nis);
      } else {
        if (!in_array($studentSMP->nis, $existingStudents)) {
          $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentSMP->nis)->first();
          $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentSMP->nis)->first();
          if ($motherIdInUser) {
            $motherID = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->first();
          } else {
            $motherID = null;
          }

          if ($fatherIdInUser) {
            $fatherID = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->first();
          } else {
            $fatherID = null;
          }
          // dd($motherID);
          $existingUsers = $this->userRepository->getAllUsers()->pluck('username')->all();
          $studentInUser = $this->userRepository->getUserByUsername($studentSMP->nis)->first();
          $dataStudent['user_id'] = $studentInUser->id;
          $dataStudent['father_ktp'] = $ktpAyah;
          $dataStudent['mother_ktp'] = $ktpIbu;
          $dataStudent['father_parent_id'] = $fatherID ? $fatherID->id : null;
          $dataStudent['mother_parent_id'] = $motherID ? $motherID->id : null;
          $dataStudent['jenjang_id'] = $this->jenjang->id;
          $dataStudent['school_id'] = $this->school->id;

          $this->studentRepository->insertStudent($dataStudent);
          array_push($existingStudents, $studentSMP->nis);
        } else {
          $this->studentRepository->updateStudentByNIY($dataStudent, $studentSMP->nis);
        }
      }
    }

    $this->studentRepository->updateStudents(['is_sibling_student' => 1], $siblingIds);
    $this->saveStudentMutation();

    //return $students;
    return ['message' => 'success'];
  }

  public function mapStudentSMPToStudent($studentSMP)
  {
    // dd($studentSMP);
    $kelas = null;
    $parallel = null;
    if ($studentSMP->siswaKelas !== null) {

      $kelasName = $this->getKelasName($studentSMP->siswaKelas->kelas);
      // dd($kelasName);
      $kelas = $this->kelasRepository->getKelasByNameAndJenjangId($kelasName, $this->jenjang->id)->first();
      $parallel = $this->parallelRepository->getParallelByNameAndKelasId($studentSMP->siswaKelas->paralel, $kelas->id)->first();
    } else {
      // dd($studentSMP);
      $kelas = null;
      $parallel = null;
    }
    if ($studentSMP->siswaEdit == null) {
      $religion = null;
    } else {
      $religion = $this->parameterRepository->getReligionParameters($studentSMP->siswaEdit->agama)->first();
    }
    if ($studentSMP->siswaNisn == null) {
      $wargaNegara = null;
      $nisn = null;
      $niy = null;
    } else {
      $wargaNegara = $studentSMP->siswaNisn->warga_negara;
      $nisn = $studentSMP->siswaNisn->nisn;
      $niy = $studentSMP->siswaNisn->niy;
    }

    if ($studentSMP->studentSMP == null) {
      dd($studentSMP);
    }
    return [
      'kelas_id' => $kelas !== null ? $kelas->id : null,
      'parallel_id' => $parallel !== null ? $parallel->id : null,
      //'masuk_tahun_id'	=> null,
      //'masuk_jenjang_id'	=> null,
      //'masuk_kelas_id'	=> null,
      //'is_father_alive'	=> 1,
      //'is_mother_alive'	=> 1,
      //'is_poor'	=> 0,
      'name' => $studentSMP->siswaEdit->nama,
      'nis' => $studentSMP->nis,
      'niy' => $niy,
      'nisn'  => $nisn,
      'nkk' => $studentSMP->siswaEdit->kkSiswa,
      //'father_ktp'	=> $studentSMP->siswaEdit->ktpAyah,
      //'mother_ktp'	=> $studentSMP->siswaEdit->ktpIbu,
      //'email'	=> null,
      'sex_type_value'  => $studentSMP->siswaEdit->jenis_kelamin === 'laki-laki' ? 1 : 2,
      'address'  => $studentSMP->siswaEdit->alamat,
      //'kota'	=> null,
      //'kecamatan'	=> null,
      //'kelurahan'	=> null,
      //'kodepos'	=> null,
      //'photo'	=> null,
      'handphone'  => $studentSMP->siswaEdit->telp,
      'birth_place'  => $studentSMP->siswaEdit->tmp_lahir,
      'birth_date'  => $studentSMP->siswaEdit->tgl_lahir,
      'birth_order'  => $studentSMP->siswaEdit->anak_ke,
      'religion_value'  => $religion !== null ? $religion->value : null,
      'nationality'  => $wargaNegara,
      //'language'	=> null,
      //'is_adopted'	=> 0,
      //'stay_with_value'	=> 1,
      //'siblings'	=> 0,
      'is_sibling_student'  => 0,
      //'foster'	=> 0,
      //'step_siblings'	=> 0,
      //'medical_history'	=> null,
      'is_active'  => $studentSMP->studentSMP->active,
      //'student_status_value'	=> 1,
      //'lulus_tahun_id'	=> null,
      //'tahun_lulus'	=> null,
      //'gol_darah'	=> null,
      //'is_cacat'	=> 0,
      //'tinggi'	=> 0,
      //'berat'	=> 0,
      'sekolah_asal'  => $studentSMP->siswaEdit->sekolah_asal
    ];
  }

  public function mapStudentSMPToFather($studentSMP)
  {
    return [
      'name' => $studentSMP->siswaEdit->nama_ayah,
      //'birth_date'=> '1890-01-01',
      'sex_type_value' => 1,
      'parent_type_value' => 1,
      //'wali_type_value'=> null,
      'job' => $studentSMP->siswaEdit->pek_ayah,
      //'jobCorporateName'=> '',
      //'jobPositionName'=> '',
      //'jobWorkAddress'=> ''
      //'ktp'=> ''
      //'nkk'=> ''
      //'email'=> ''
      //'mobilePhone'=> ''
    ];
  }

  public function mapStudentSMPToMother($studentSMP)
  {
    return [
      'name' => $studentSMP->siswaEdit->nama_ibu,
      //'birth_date'=> '1890-01-01',
      'sex_type_value' => 2,
      'parent_type_value' => 2,
      //'wali_type_value'=> null,
      'job' => $studentSMP->siswaEdit->pek_ibu,
      //'jobCorporateName'=> '',
      //'jobPositionName'=> '',
      //'jobWorkAddress'=> ''
      //'ktp'=> ''
      //'nkk'=> ''
      //'email'=> ''
      //'mobilePhone'=> ''
    ];
  }

  public function syncStudentSMA($tahun_pelajaran_id)
  {
    set_time_limit(0);
    $father_parent_id = null;
    $mother_parent_id = null;
    $this->jenjang = $this->jenjangRepository->getJenjangByCode('SMA')->first();
    $this->school = $this->schoolRepository->getSchoolByJenjang($this->jenjang->id)->first();
    if (isset($tahun_pelajaran_id)) {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranById($tahun_pelajaran_id)->first();
    } else {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();
    }
    $roleStudent = $this->roleRepository->getRoleByCode('STUDENT')->first();
    $roleParent = $this->roleRepository->getRoleByCode('PARENT')->first();
    $existingStudents = $this->studentRepository->getAllStudents()->pluck('niy')->all();

    $existingUsers = $this->userRepository->getAllUsers()->pluck('username')->all();
    $studentSMAs = $this->studentRepository->getStudentSMAWithBukuInduk($this->tahunActive->name)->get();



    $siblingIds = [];
    foreach ($studentSMAs as $studentSMA) {

      $dataStudent = $this->mapStudentSMAToStudent($studentSMA);
      if ($studentSMA->bukuInduk !== null) {
        $kkAyah = strlen($studentSMA->bukuInduk->kkAyah) < 16 ? null : $studentSMA->bukuInduk->kkAyah;
        $ktpAyah = strlen($studentSMA->bukuInduk->ktpAyah) < 16 ? null : $studentSMA->bukuInduk->ktpAyah;
        $emailAyah = $studentSMA->bukuInduk->emailayah === "" ? null : $studentSMA->bukuInduk->emailayah;
        $mobilePhoneAyah = strlen($studentSMA->bukuInduk->hpayah) < 5 ? null : $studentSMA->bukuInduk->hpayah;

        $kkIbu = strlen($studentSMA->bukuInduk->kkIbu) < 16 ? null : $studentSMA->bukuInduk->kkIbu;
        $ktpIbu = strlen($studentSMA->bukuInduk->ktpIbu) < 16 ? null : $studentSMA->bukuInduk->ktpIbu;
        $emailIbu = $studentSMA->bukuInduk->emailibu === "" ? null : $studentSMA->bukuInduk->emailibu;
        $mobilePhoneIbu = strlen($studentSMA->bukuInduk->hpibu) < 5 ? null : $studentSMA->bukuInduk->hpibu;

        $existingFather = null;

        if ($ktpAyah !== null) {
          $existingFather = $this->parentRepository->getExistingParentByKtp($ktpAyah, 1)->first();
        }
        if ($existingFather === null && $kkAyah !== null) {
          $existingFather = $this->parentRepository->getExistingParentByKk($kkAyah, 1)->first();
        }
        if ($existingFather === null && $mobilePhoneAyah !== null) {
          $existingFather = $this->parentRepository->getExistingParentByMobilePhone($mobilePhoneAyah, 1)->first();
        }



        $existingMother = null;

        if ($ktpIbu !== null) {
          $existingMother = $this->parentRepository->getExistingParentByKtp($ktpIbu, 2)->first();
        }
        if ($existingMother === null && $kkIbu !== null) {
          $existingMother = $this->parentRepository->getExistingParentByKk($kkIbu, 2)->first();
        }
        if ($existingMother === null && $mobilePhoneIbu !== null) {
          $existingMother = $this->parentRepository->getExistingParentByMobilePhone($mobilePhoneIbu, 2)->first();
        }

        //array_push($test,$existingMother);
        //continue;
        //father
        if ($existingFather) {
          $father_parent_id = $existingFather->id;
          $resultResultIds = $this->studentRepository->getSiblingByFatherId($studentSMA->niy, $father_parent_id)->pluck('id');
          if (isset($resultResultIds[0])) {
            $siblingIds = array_merge($siblingIds, $resultResultIds->all());
          }
        } else {
          if (!in_array('F' . $studentSMA->niy, $existingUsers)) {
            $user_father_id = $this->userRepository->insertUserGetId(
              [
                'status_active' => 99,
                'name' => $studentSMA->bukuInduk->namaayah,
                'email' => $studentSMA->bukuInduk->emailayah,
                'username' => 'F' . $studentSMA->niy,
                'password' => bcrypt('F' . $studentSMA->niy),
                'user_type_value' => 3
              ]
            );
            $dataFather = $this->mapStudentSMAToFather($studentSMA);
            $dataFather['user_id'] = $user_father_id;
            $dataFather['ktp'] = $ktpAyah;
            $dataFather['nkk'] =  $kkAyah;
            $dataFather['email'] = $emailAyah;
            $dataFather['mobilePhone'] = $mobilePhoneAyah;

            $father_parent_id = $this->parentRepository->insertParentGetId($dataFather);
            $this->roleRepository->syncUserRole($user_father_id, $roleParent->id);
            array_push($existingUsers, 'F' . $studentSMA->niy);
          } else {
            $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentSMA->niy)->first();
            $existingFatherInParent = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->pluck('id')->all();
            if ($existingFatherInParent == null) {
              $dataFather = $this->mapStudentSMAToFather($studentSMA);
              $dataFather['user_id'] = $fatherIdInUser->id;
              $dataFather['ktp'] = $ktpAyah;
              $dataFather['nkk'] =  $kkAyah;
              $dataFather['email'] = $emailAyah;
              $dataFather['mobilePhone'] = $mobilePhoneAyah;


              $father_parent_id = $this->parentRepository->insertParentGetId($dataFather);
              // dd($father_parent_id);
              $this->roleRepository->syncUserRole($fatherIdInUser->id, $roleParent->id);
            }
          }
        }


        //mother
        if ($existingMother) {
          $mother_parent_id = $existingMother->id;
          $resultResultIds = $this->studentRepository->getSiblingByMotherId($studentSMA->niy, $mother_parent_id)->pluck('id');
          if (isset($resultResultIds[0])) {
            $siblingIds = array_merge($siblingIds, $resultResultIds->all());
          }
        } else {
          if (!in_array('M' . $studentSMA->niy, $existingUsers)) {
            $user_mother_id = $this->userRepository->insertUserGetId(
              [
                'status_active' => 99,
                'name' => $studentSMA->bukuInduk->namaibu,
                'email' => $studentSMA->bukuInduk->emailibu,
                'username' => 'M' . $studentSMA->niy,
                'password' => bcrypt('M' . $studentSMA->niy),
                'user_type_value' => 3
              ]
            );

            $dataMother = $this->mapStudentSMAToMother($studentSMA);
            $dataMother['user_id'] = $user_mother_id;
            $dataMother['ktp'] = $ktpIbu;
            $dataMother['nkk'] =  $kkIbu;
            $dataMother['email'] = $emailIbu;
            $dataMother['mobilePhone'] = $mobilePhoneIbu;

            $mother_parent_id = $this->parentRepository->insertParentGetId($dataMother);
            $this->roleRepository->syncUserRole($user_mother_id, $roleParent->id);
            array_push($existingUsers, 'M' . $studentSMA->niy);
          } else {
            $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentSMA->niy)->first();
            $existingMotherInParent = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->pluck('id')->all();
            if ($existingMotherInParent == null) {
              // dd($motherIdInUser);
              $dataMother = $this->mapStudentSMAToMother($studentSMA);
              $dataMother['user_id'] = $motherIdInUser->id;
              $dataMother['ktp'] = $ktpIbu;
              $dataMother['nkk'] =  $kkIbu;
              $dataMother['email'] = $emailIbu;
              $dataMother['mobilePhone'] = $mobilePhoneIbu;


              $mother_parent_id = $this->parentRepository->insertParentGetId($dataMother);
              // dd($father_parent_id);
              $this->roleRepository->syncUserRole($motherIdInUser->id, $roleParent->id);
            }
          }
        }

        //student
        if (!in_array($studentSMA->niy, $existingUsers)) {
          $user_student_id = $this->userRepository->insertUserGetId(
            [
              'status_active' => 99,
              'name' => $studentSMA->nama,
              'email' => $studentSMA->bukuInduk->email,
              'username' => $studentSMA->niy,
              'password' => bcrypt($studentSMA->niy),
              'user_type_value' => 2
            ]
          );


          $dataStudent['user_id'] = $user_student_id;
          $dataStudent['father_ktp'] = $ktpAyah;
          $dataStudent['mother_ktp'] = $ktpIbu;
          $dataStudent['father_parent_id'] = $father_parent_id;
          $dataStudent['mother_parent_id'] = $mother_parent_id;
          $dataStudent['jenjang_id'] = $this->jenjang->id;
          $dataStudent['school_id'] = $this->school->id;


          $this->studentRepository->insertStudent($dataStudent);
          $this->roleRepository->syncUserRole($user_student_id, $roleStudent->id);
          array_push($existingUsers, $studentSMA->niy);
        } else {
          // dd($existingStudents);
          if (!in_array($studentSMA->niy, $existingStudents)) {
            $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentSMA->niy)->first();
            $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentSMA->niy)->first();

            if ($motherIdInUser) {
              $motherID = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->first();
            } else {
              $motherID = null;
            }

            if ($fatherIdInUser) {
              $fatherID = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->first();
            } else {
              $fatherID = null;
            }

            $studentInUser = $this->userRepository->getUserByUsername($studentSMA->niy)->first();
            $dataStudent['user_id'] = $studentInUser->id;
            $dataStudent['father_ktp'] = $ktpAyah;
            $dataStudent['mother_ktp'] = $ktpIbu;
            $dataStudent['father_parent_id'] = $fatherID ? $fatherID->id : null;
            $dataStudent['mother_parent_id'] = $motherID ? $motherID->id : null;
            $dataStudent['jenjang_id'] = $this->jenjang->id;
            $dataStudent['school_id'] = $this->school->id;

            $this->studentRepository->insertStudent($dataStudent);
            array_push($existingStudents, $studentSMA->niy);
          } else {
            $this->studentRepository->updateStudentByNIY($dataStudent, $studentSMA->niy);
          }
        }
      } else {
        $ktpAyah = null;
        $ktpIbu = null;
        $father_parent_id = null;
        $mother_parent_id = null;
        //student
        if (!in_array($studentSMA->niy, $existingUsers)) {
          $user_student_id = $this->userRepository->insertUserGetId(
            [
              'status_active' => 99,
              'name' => $studentSMA->nama,
              'email' => $studentSMA->bukuInduk ? $studentSMA->bukuInduk->email : null,
              'username' => $studentSMA->niy,
              'password' => bcrypt($studentSMA->niy),
              'user_type_value' => 2
            ]
          );


          $dataStudent['user_id'] = $user_student_id;
          $dataStudent['father_ktp'] = $ktpAyah;
          $dataStudent['mother_ktp'] = $ktpIbu;
          $dataStudent['father_parent_id'] = $father_parent_id;
          $dataStudent['mother_parent_id'] = $mother_parent_id;
          $dataStudent['jenjang_id'] = $this->jenjang->id;
          $dataStudent['school_id'] = $this->school->id;


          $this->studentRepository->insertStudent($dataStudent);
          $this->roleRepository->syncUserRole($user_student_id, $roleStudent->id);
          array_push($existingUsers, $studentSMA->niy);
        } else {
          // dd($existingStudents);
          if (!in_array($studentSMA->niy, $existingStudents)) {
            $motherIdInUser = $this->userRepository->getUserByUsername('M' . $studentSMA->niy)->first();
            $fatherIdInUser = $this->userRepository->getUserByUsername('F' . $studentSMA->niy)->first();
            if ($motherIdInUser) {
              $motherID = $this->parentRepository->getExistingParentbyUserId($motherIdInUser->id, 2)->first();
            } else {
              $motherID = null;
            }

            if ($fatherIdInUser) {
              $fatherID = $this->parentRepository->getExistingParentbyUserId($fatherIdInUser->id, 1)->first();
            } else {
              $fatherID = null;
            }

            $studentInUser = $this->userRepository->getUserByUsername($studentSMA->niy)->first();
            $dataStudent['user_id'] = $studentInUser->id;
            $dataStudent['father_ktp'] = $ktpAyah;
            $dataStudent['mother_ktp'] = $ktpIbu;
            $dataStudent['father_parent_id'] = $fatherID ? $fatherID->id : null;
            $dataStudent['mother_parent_id'] = $motherID ? $motherID->id : null;
            $dataStudent['jenjang_id'] = $this->jenjang->id;
            $dataStudent['school_id'] = $this->school->id;

            $this->studentRepository->insertStudent($dataStudent);
            array_push($existingStudents, $studentSMA->niy);
          } else {
            $this->studentRepository->updateStudentByNIY($dataStudent, $studentSMA->niy);
          }
        }
      }
    }

    $this->studentRepository->updateStudents(['is_sibling_student' => 1], $siblingIds);
    $this->saveStudentMutation();

    //return $students;
    return ['message' => 'success'];
  }

  public function mapStudentSMAToStudent($studentSMA)
  {
    $jurusan = (int)$studentSMA->kelasSMA->jurusan;
    $kelasName = $this->getKelasName($studentSMA->kelasSMA->kelas);
    $kelas = $this->kelasRepository->getKelasByNameAndJenjangId($kelasName, $this->jenjang->id)->first();
    $parallel = $this->parallelRepository->getParallelByNameAndKelasIdAndJurusanID($studentSMA->kelasSMA->pararel, $kelas->id, $jurusan)->first();
    if ($studentSMA->bukuInduk == null) {
      // dd($studentSMA->bukuInduk);
      $religion = null;
      $email = null;
      $jk = 1;
      $thnLahir = 9999;
      $blnLahir = 'September';
      $tglLahir = 9;
    } else {
      $religion = $this->parameterRepository->getReligionParameters($studentSMA->bukuInduk->agama)->first();
      $email = $studentSMA->bukuInduk->email;
      $jk = $studentSMA->bukuInduk->jk;
      if ($jk == 'l') {
        $jk = 1;
      } else {
        $jk = 2;
      }
      $thnLahir = $studentSMA->bukuInduk->thnLahir;
      $blnLahir = $studentSMA->bukuInduk->blnLahir;
      $tglLahir = $studentSMA->bukuInduk->tglLahir;
    }


    return [
      'kelas_id' => $kelas !== null ? $kelas->id : null,
      'parallel_id' => $parallel !== null ? $parallel->id : null,
      //'masuk_tahun_id'	=> null,
      //'masuk_jenjang_id'	=> null,
      //'masuk_kelas_id'	=> null,
      //'is_father_alive'	=> 1,
      //'is_mother_alive'	=> 1,
      //'is_poor'	=> 0,
      'name' => $studentSMA->nama,
      'nis' => $studentSMA->nis,
      'niy' => $studentSMA->niy,
      'nisn'  => $studentSMA->nin,
      'nkk' => $studentSMA->bukuInduk ? $studentSMA->bukuInduk->kkSiswa : null,
      //'father_ktp'	=> $studentSMA->bukuInduk ? $studentSMA->bukuInduk->ktpAyah:null,
      //'mother_ktp'	=> $studentSMA->bukuInduk ? $studentSMA->bukuInduk->ktpIbu:null,
      'email'  => $email,
      'sex_type_value'  => $jk,
      'address'  => $studentSMA->bukuInduk ? $studentSMA->bukuInduk->alamat : null,
      //'kota'	=> null,
      //'kecamatan'	=> null,
      //'kelurahan'	=> null,
      //'kodepos'	=> null,
      //'photo'	=> null,
      'handphone'  => $studentSMA->bukuInduk ? $studentSMA->bukuInduk->hp : null,
      'birth_place'  => $studentSMA->bukuInduk ? $studentSMA->bukuInduk->tlahir : null,
      'birth_date'  => $thnLahir . "-" . $this->getIndonesianMonthNumber($blnLahir) . "-" . $tglLahir,
      'birth_order'  => $studentSMA->bukuInduk ? $studentSMA->bukuInduk->anakKe : 1,
      'religion_value'  => $religion !== null ? $religion->value : null,
      'nationality'  => $studentSMA->bukuInduk ? $studentSMA->bukuInduk->warga : null,
      //'language'	=> null,
      //'is_adopted'	=> 0,
      //'stay_with_value'	=> 1,
      //'siblings'	=> 0,
      'is_sibling_student'  => 0,
      //'foster'	=> 0,
      //'step_siblings'	=> 0,
      //'medical_history'	=> null,
      //'is_active'	=> $studentSMA->active,
      //'student_status_value'	=> 1,
      //'lulus_tahun_id'	=> null,
      //'tahun_lulus'	=> null,
      //'gol_darah'	=> null,
      //'is_cacat'	=> 0,
      //'tinggi'	=> 0,
      //'berat'	=> 0,
      'sekolah_asal'  => $studentSMA->bukuInduk ? $studentSMA->bukuInduk->sekolahAsal : null
    ];
  }

  public function mapStudentSMAToFather($studentSMA)
  {
    return [
      'name' => $studentSMA->bukuInduk->namaayah,
      //'birth_date'=> '1890-01-01',
      'sex_type_value' => 1,
      'parent_type_value' => 1,
      //'wali_type_value'=> null,
      'job' => $studentSMA->bukuInduk->pekerjaanayah,
      //'jobCorporateName'=> '',
      //'jobPositionName'=> '',
      //'jobWorkAddress'=> ''
      //'ktp'=> ''
      //'nkk'=> ''
      //'email'=> ''
      //'mobilePhone'=> ''
    ];
  }

  public function mapStudentSMAToMother($studentSMA)
  {
    return [
      'name' => $studentSMA->bukuInduk->namaibu,
      //'birth_date'=> '1890-01-01',
      'sex_type_value' => 1,
      'parent_type_value' => 1,
      //'wali_type_value'=> null,
      'job' => $studentSMA->bukuInduk->pekerjaanibu,
      //'jobCorporateName'=> '',
      //'jobPositionName'=> '',
      //'jobWorkAddress'=> ''
      //'ktp'=> ''
      //'nkk'=> ''
      //'email'=> ''
      //'mobilePhone'=> ''
    ];
  }

  public function syncStudentPCI($tahun_pelajaran_id)
  {
    if (isset($tahun_pelajaran_id)) {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranById($tahun_pelajaran_id)->first();
    } else {
      $this->tahunActive = $this->tahunPelajaranRepository->getTahunPelajaranActive()->first();
    }

    return $this->studentRepository->getStudentPCI($this->tahunActive->name)->get();
  }

  public function deleteStudents($ids)
  {
    $this->studentRepository->deleteStudents($ids);
  }

  function getIndonesianMonthNumber($monthName)
  {
    if ($monthName === 'Januari') {
      return '01';
    } elseif ($monthName === 'Februari') {
      return '02';
    } elseif ($monthName === 'Maret') {
      return '03';
    } elseif ($monthName === 'April') {
      return '04';
    } elseif ($monthName === 'Mei') {
      return '05';
    } elseif ($monthName === 'Juni') {
      return '06';
    } elseif ($monthName === 'Juli') {
      return '07';
    } elseif ($monthName === 'Agustus') {
      return '08';
    } elseif ($monthName === 'September') {
      return '09';
    } elseif ($monthName === 'Oktober') {
      return '10';
    } elseif ($monthName === 'November') {
      return '11';
    } elseif ($monthName === 'Desember') {
      return '12';
    }
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
