<?php

namespace App\Exports;

use App\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Illuminate\Support\Collection;

class StudentExcel implements FromArray
{
    protected $dataCollection;

    public function __construct(Collection $dataCollection)
    {
        $this->dataCollection = $dataCollection;
    }

    public function collection()
    {
        return $this->dataCollection->all();
    }

    public function array(): array
    {

        $siblingCount = 0;
        $students = [
            ['Name', 'NIY', 'Jenjang', 'Kelas', 'Parallel', 'Jurusan']
        ];
        foreach ($this->dataCollection as $student) {

            $studentArray = [
                $student->name,
                $student->niy,
                $student->jenjang ? $student->jenjang->code : "",
                $student->kelas ? $student->kelas->name : "",
                $student->parallel ? $student->parallel->name : "",
                $student->parallel->jurusan ? $student->parallel->jurusan->code : ""
            ];
            // dd($student->parallel->jurusan);
            if ($student->sibling_students) {
                if (count($student->sibling_students) > $siblingCount) {
                    array_push($students[0], 'Name');
                    array_push($students[0], 'Jenjang');
                    array_push($students[0], 'Kelas');
                    array_push($students[0], 'Parallel');
                    array_push($students[0], 'Jurusan');
                    $siblingCount++;
                }
                foreach ($student->sibling_students as $sibling) {
                    array_push($studentArray, $sibling->name);
                    array_push($studentArray, $sibling->jenjang ? $sibling->jenjang->code : "");
                    array_push($studentArray, $sibling->kelas ? $sibling->kelas->name : "");
                    array_push($studentArray, $sibling->parallel ? $sibling->parallel->name : "");
                    array_push($student->parallel->jurusan->code ? $student->parallel->jurusan->code : "");
                }
            }

            array_push($students, $studentArray);
        }

        return $students;
    }
}
