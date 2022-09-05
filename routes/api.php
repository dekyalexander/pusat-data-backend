<?php

use App\Http\Controllers\ApplicationCategoriesController;
use App\Http\Controllers\CategoryApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//auth token
Route::get('/auth', 'AuthController@auth')->name('auth');
Route::post('/login', 'AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {

  //actions
  Route::get('/action', 'ActionController@data');
  Route::get('/action/option', 'ActionController@getForOptions');
  Route::get('/action/role', 'ActionController@getForRoles');
  Route::put('/action/approver', 'ActionController@addApprover');
  Route::get('/action/approver', 'ActionController@getApproverOfAction');
  Route::post('/action', 'ActionController@store');
  Route::get('/action/{id}', 'ActionController@edit');
  Route::put('/action', 'ActionController@update');
  Route::delete('/action', 'ActionController@destroy');
  Route::delete('/action/approver', 'ActionController@deleteApprover');

  //applications
  Route::get('/application', 'ApplicationController@data');
  Route::get('/application/option', 'ApplicationController@getForOptions');
  Route::get('/application/menu', 'ApplicationController@getMenusOfApplication');
  Route::post('/application', 'ApplicationController@store');
  Route::get('/application/{id}', 'ApplicationController@edit');
  Route::put('/application', 'ApplicationController@update');
  Route::put('/application/menu', 'ApplicationController@addMenuOfApplication');
  Route::delete('/application/menu', 'ApplicationController@deleteMenuOfApplication');
  Route::delete('/application', 'ApplicationController@destroy');

  //approvals
  Route::get('/approval', 'ApprovalController@data');
  Route::post('/approval', 'ApprovalController@store');
  Route::get('/approval/{id}', 'ApprovalController@edit');
  Route::put('/approval', 'ApprovalController@update');
  Route::delete('/approval', 'ApprovalController@destroy');

  Route::get('/employee', 'EmployeeController@getEmployeesByFilters');
  Route::get('/employee/option', 'EmployeeController@getEmployeeOptions');
  Route::post('/employee', 'EmployeeController@createEmployee');
  Route::put('/employee', 'EmployeeController@updateEmployee');
  Route::delete('/employee', 'EmployeeController@deleteEmployees');

  Route::get('/jenjang', 'JenjangController@getJenjangsByFilters');
  Route::get('/jenjang/option', 'JenjangController@getJenjangOptions');
  Route::post('/jenjang', 'JenjangController@createJenjang');
  Route::put('/jenjang', 'JenjangController@updateJenjang');
  Route::delete('/jenjang', 'JenjangController@deleteJenjangs');

  Route::get('/jurusan', 'JurusanController@getJurusansByFilters');
  Route::get('/jurusan/option', 'JurusanController@getJurusanOptions');
  Route::post('/jurusan', 'JurusanController@createJurusan');
  Route::put('/jurusan', 'JurusanController@updateJurusan');
  Route::delete('/jurusan', 'JurusanController@deleteJurusans');
  Route::post('/jurusan/sync', 'JurusanController@syncJurusan');

  Route::get('/kelas', 'KelasController@getKelassByFilters');
  Route::get('/kelas/option', 'KelasController@getKelasOptions');
  Route::post('/kelas', 'KelasController@createKelas');
  Route::put('/kelas', 'KelasController@updateKelas');
  Route::delete('/kelas', 'KelasController@deleteKelass');
  Route::post('/kelas/sync', 'KelasController@syncKelas');

  //menus
  Route::get('/menu', 'MenuController@data');
  Route::get('/menu/option', 'MenuController@getForOptions');
  Route::get('/menu/role', 'MenuController@getRoleOfMenu');
  Route::get('/menu/action', 'MenuController@getActionOfMenu');
  Route::post('/menu', 'MenuController@store');
  Route::get('/menu/{id}', 'MenuController@edit');
  Route::put('/menu', 'MenuController@update');
  Route::delete('/menu', 'MenuController@destroy');
  Route::delete('/menu/action', 'MenuController@deleteActionOfMenu');


  //parameters
  Route::get('/parameter', 'ParameterController@data');
  Route::get('/parameter/option', 'ParameterController@getForOptions');
  Route::post('/parameter', 'ParameterController@store');
  Route::get('/parameter/{id}', 'ParameterController@detail');
  Route::put('/parameter', 'ParameterController@update');
  Route::delete('/parameter', 'ParameterController@destroy');

  // parallel
  Route::get('/parallel', 'ParallelController@getParallelsByFilters');
  Route::get('/parallel/option', 'ParallelController@getParallelOptions');
  Route::post('/parallel', 'ParallelController@createParallel');
  Route::put('/parallel', 'ParallelController@updateParallel');
  Route::delete('/parallel', 'ParallelController@deleteParallels');
  Route::post('/parallel/sync', 'ParallelController@syncParallel');

  // parent
  Route::get('/parent', 'ParentController@getParentsByFilters');
  Route::get('/parent/option', 'ParentController@getParentOptions');
  Route::post('/parent', 'ParentController@createParent');
  Route::put('/parent', 'ParentController@updateParent');
  Route::delete('/parent', 'ParentController@deleteParents');
  Route::get('/parent/student', 'ParentController@getStudentsOfParent');
  Route::get('/parent/{parent_id}', 'ParentController@getParentDetail');
  Route::put('/parent/student', 'ParentController@addStudentsToParent');

  //roles
  Route::get('/role', 'RoleController@data');
  Route::get('/role/user', 'RoleController@getUsersOfRole');
  Route::get('/role/option', 'RoleController@getForOptions');
  Route::get('/role/approval', 'RoleController@getApprovalsOfRole');
  Route::put('/role/approval', 'RoleController@addApproval');
  Route::put('/role/priviledge', 'RoleController@storePriviledge');
  Route::put('/role/user', 'RoleController@addUserOfRole');
  Route::post('/role', 'RoleController@store');
  Route::get('/role/{id}', 'RoleController@detail');
  Route::put('/role', 'RoleController@update');
  Route::delete('/role', 'RoleController@destroy');
  Route::delete('/role/user', 'RoleController@deleteUserOfRole');
  Route::delete('/role/approval', 'RoleController@deleteApprovalOfRole');
  Route::post('/role/byPosition', 'RoleController@addUserByPosition');


  Route::get('/student', 'StudentController@getStudentsByFilters');
  Route::get('/student/option', 'StudentController@getStudentOptions');
  Route::post('/student', 'StudentController@createStudent');
  Route::put('/student', 'StudentController@updateStudent');
  Route::delete('/student', 'StudentController@deleteStudents');
  Route::get('/student/parent', 'StudentController@getParentsOfStudent');
  Route::post('/student/sync', 'StudentController@syncStudent');
  Route::get('/student/mutation', 'StudentController@getMutationsOfStudent');
  Route::get('/student/sibling', 'StudentController@getSiblingsOfStudent');
  Route::get('/student/{student_id}', 'StudentController@getStudentDetail');


  Route::get('/school', 'SchoolController@getSchoolsByFilters');
  Route::get('/school/option', 'SchoolController@getSchoolOptions');
  Route::post('/school', 'SchoolController@createSchool');
  Route::put('/school', 'SchoolController@updateSchool');
  Route::delete('/school', 'SchoolController@deleteSchools');


  Route::get('/tahun-pelajaran', 'TahunPelajaranController@getTahunPelajaransByFilters');
  Route::get('/tahun-pelajaran/option', 'TahunPelajaranController@getTahunPelajaranOptions');
  Route::post('/tahun-pelajaran', 'TahunPelajaranController@createTahunPelajaran');
  Route::put('/tahun-pelajaran', 'TahunPelajaranController@updateTahunPelajaran');
  Route::delete('/tahun-pelajaran', 'TahunPelajaranController@deleteTahunPelajarans');

  //units
  Route::get('/unit', 'UnitController@data');
  Route::get('/unit/option', 'UnitController@getForOptions');
  Route::post('/unit', 'UnitController@store');
  Route::post('/unit/employee', 'UnitController@storeOfEmployes');
  Route::get('/unit/{id}', 'UnitController@edit');
  Route::put('/unit', 'UnitController@update');
  Route::delete('/unit', 'UnitController@destroy');

  // employeeUnits
  Route::get('/employeeUnit', 'EmployeeUnitController@data');
  Route::get('/employeeUnit/option', 'EmployeeUnitController@getForOptions');

  // position
  Route::get('/postion/byUnitId', 'PositionController@getByUnitId');
  Route::get('/position/occupations', 'PositionController@getOccupationsId');


  //users
  Route::get('/user', 'UserController@getUsersByFilters');
  Route::get('/user/by-token', 'UserController@getUserByToken');
  Route::get('/user/option', 'UserController@getForOptions');
  Route::get('/user/unit', 'UserController@getUnitOfUser');
  Route::get('/user/role', 'UserController@getRoleOfUser');
  Route::put('/user/unit', 'UserController@addUnit');
  Route::get('/user/my', 'UserController@myUser');
  Route::post('/user', 'UserController@store');
  Route::post('/user/employee', 'UserController@storeOfEmployes');
  Route::put('/user/employee', 'UserController@updateOfEmployes');
  Route::put('/user/change-password', 'UserController@changePassword');
  Route::put('/user/reset-password', 'UserController@resetPassword');
  Route::get('/user/{id}', 'UserController@edit');
  Route::put('/user', 'UserController@update');
  Route::put('/user/role', 'UserController@addRole');
  Route::delete('/user', 'UserController@destroy');
  Route::delete('/user/role', 'UserController@deleteRoleOfUser');
  Route::get('/users/of-role', 'RoleUserController@getUserOfRole');

  //notification
  Route::get('/notification', [App\Http\Controllers\NotificationController::class, 'getData']);
  Route::get('/notification/total-unread', [App\Http\Controllers\NotificationController::class, 'getTotalUnRead']);
  Route::put('/notification/update', [App\Http\Controllers\NotificationController::class, 'update']);

  // application categories
  Route::get('/application-categories', 'ApplicationCategoriesController@getApplicationCategories');
  Route::post('/application-categories', 'ApplicationCategoriesController@insertAplicationCategories');
  Route::delete('/application-categories', 'ApplicationCategoriesController@deleteApplicationCategories');

  // category Aplikasi
  Route::get('/category', 'CategoryApplicationController@getCategory');
  Route::post('/category', 'CategoryApplicationController@insertCategory');
  Route::delete('/category', 'CategoryApplicationController@deleteCategory');
  Route::put('/category', 'CategoryApplicationController@updateCategory');
});
