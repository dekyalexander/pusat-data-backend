<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUser extends Pivot
{
    protected $table = 'role_users';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
