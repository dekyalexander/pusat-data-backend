<?php

namespace App\Repositories;

use App\Models\Employee;
use Carbon\Carbon;

class EmployeeRepository{
  protected $employee;

  public function __construct(Employee $employee){
    $this->employee = $employee;
  }

  public function getEmployeeById($id,$selects=['*']){
    return Employee::select($selects)
    ->where('id','=',$id);
  }

  public function getEmployeesByFilters($filters)
  {
    return  
    Employee::with([
      'user'
      ])
    ->when(isset($filters['keyword']), function ($query) use ($filters) {
      return $query->orWhere('name','like','%'.$filters['keyword'].'%');
    })
    ->when(isset($filters['name']), function ($query) use ($filters) {
        return $query->where('name','like','%'.$filters['name'].'%');
    });
  }

  public function getEmployeeOptions($keyword){
    return Employee::select('id','name')
    ->when(isset($keyword), function ($query) use ($keyword) {
      return $query->where('name','like','%'.$keyword.'%');
    });
  }

  public function insertEmployee($data){
    Employee::insert($data);
  }

  public function insertEmployeeGetId($data){
    return Employee::insertGetId($data);
  }

  public function insertGetEmployee($data){
    return Employee::create($data);
  }

  public function updateEmployee($data,$id){
    Employee::where('id', $id)
            ->update($data);
  }
  
  public function deleteEmployees($ids){
    Employee::whereIn('id', $ids)
            ->delete();
  }
}
