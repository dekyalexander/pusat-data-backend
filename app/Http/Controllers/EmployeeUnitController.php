<?php

namespace App\Http\Controllers;

use App\Services\EmployeeUnitService;
use Illuminate\Http\Request;
use App\Services\UnitService;

class EmployeeUnitController extends Controller
{

    public $success = 200;
    public $unauth = 401;
    public $error = 500;
    public $conflict = 409;

    protected $employeeUnitService;

    public function __construct(EmployeeUnitService $employeeUnitService)
    {
        $this->employeeUnitService = $employeeUnitService;
    }

    public function data(Request $request)
    {
        $reqParams = $request->all();

        if ($request->keyword) {
            $keys = ['name' => $request->keyword];
            $reqParams = array_merge($reqParams, $keys);
        }

        try {
            $query = $this->employeeUnitService->data($reqParams);

            if ($request->page) {
                $result = $query->paginate($request->rowsPerPage);
            } else {
                $result = $query->get();
            }

            return response($result);
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage(), 'message' => 'failed get data']);
        }
    }

    public function getForOptions()
    {
        try {
            $query = $this->employeeUnitService->getForOptions();
            return response($query);
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage(), 'message' => 'failed get data']);
        }
    }
}
