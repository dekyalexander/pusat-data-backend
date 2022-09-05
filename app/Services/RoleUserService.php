<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;
use App\Repositories\RoleUserRepository;

class RoleUserService
{

    protected $roleUserRepo;

    public function __construct(RoleUserRepository $roleUserRepo)
    {
        $this->roleUserRepo = $roleUserRepo;
    }


    public function getByOptionFilter($filters, $rowsPerPage)
    {
        $res = $this->roleUserRepo->getForOptions($filters)->paginate($rowsPerPage);
        $data = $res->toArray();
        $response = $data["data"];
        $arr = [];
        foreach ($response as $key) {
            array_push($arr, $key['user']);
        }
        $data["data"] = $arr;
        return $data;
    }

    public function getRoleUserByFilter($filters)
    {
        return $this->roleUserRepo->getForOptions($filters);
    }
}
