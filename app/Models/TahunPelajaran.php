<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
id	bigint(20) UN AI PK
name	varchar(255)
start_date	date
end_date	date
is_active	tinyint(4)
created_at	timestamp
updated_at	timestamp
*/
class TahunPelajaran extends Model {
    protected $table="tahun_pelajarans";    
    
}
