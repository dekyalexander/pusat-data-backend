<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnittypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('unit_types')->insert([
            'name_type_unit' => "Akademik",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('unit_types')->insert([
            'name_type_unit' => "Nonakademik",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
