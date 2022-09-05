<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Unit::create([
            'name' => 'Direktur Eksekutif  Akademik',
            'head_role_id' => '1',
            'unit_type_value' => '1',
        ]);

        Unit::create([
            'name' => 'BP4',
            'head_role_id' => '1',
            'unit_type_value' => '2'
        ]);

        Unit::create([
            'name' => 'Direktur Eksekutif Non Akademik',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'TK',
            'head_role_id' => '1',
            'unit_type_value' => '1',
        ]);

        Unit::create([
            'name' => 'SD',
            'head_role_id' => '1',
            'unit_type_value' => '1',
        ]);

        Unit::create([
            'name' => 'SMP',
            'head_role_id' => '1',
            'unit_type_value' => '1',
        ]);

        Unit::create([
            'name' => 'SMA',
            'head_role_id' => '1',
            'unit_type_value' => '1',
        ]);

        Unit::create([
            'name' => 'SDM',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'SARUM',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Keuangan',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Akunting',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Sekretariat',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'IT',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Internal Audit',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Humas dan Promosi',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Pembelian',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'BPPM',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'UJPS',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'PCI',
            'head_role_id' => '1',
            'unit_type_value' => '1',
        ]);

        Unit::create([
            'name' => 'UKS',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Management Trainee',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Sementara (Data Temporary)',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

        Unit::create([
            'name' => 'Perpustakaan',
            'head_role_id' => '1',
            'unit_type_value' => '1',
        ]);


        Unit::create([
            'name' => 'Dokumen Kontrol',
            'head_role_id' => '1',
            'unit_type_value' => '2',
        ]);

    }
}
