<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\RoleAction;
use App\Models\RoleApproval;
use App\Models\RoleUser;
use App\Models\RoleMenu;
use Illuminate\Support\Facades\DB;

class RoleUserRepository
{
    protected $roleUser;

    public function __construct(RoleUser $roleUser)
    {
        $this->roleUser = $roleUser;
    }

    public function getForOptions($request)
    {
        return $this->roleUser->with(['user'])
            ->when(isset($request['userId']), function ($query) use ($request) {
                return $query->where('user_id', $request['userId']);
            })->when(isset($request['roleId']), function ($query) use ($request) {
                return $query->where('role_id', $request['roleId']);
            });
    }
}
