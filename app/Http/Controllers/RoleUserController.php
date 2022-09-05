<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ParentService;
use App\Services\RoleUserService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoleUserController extends Controller
{
    protected $roleUserService;

    public function __construct(RoleUserService $roleUserService)
    {
        $this->roleUserService = $roleUserService;
    }

    public function getUserOfRole(Request $request)
    {
        $filters = $request->all();
        return $this->roleUserService->getByOptionFilter($filters, $request->rowsPerPage);
    }
}
