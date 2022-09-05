<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeUnitIdToJenjangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // isinya di input langsung yaa di databasenya. gapake seeder
        Schema::table('jenjangs', function (Blueprint $table) {
            $table->smallInteger('employee_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jenjangs', function (Blueprint $table) {
            $table->smallInteger('employee_unit_id');
        });
    }
}
