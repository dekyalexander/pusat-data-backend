<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTahunPelajaranToParallelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parallels', function (Blueprint $table) {
            $table->string('tahun_pelajaran')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parallels', function (Blueprint $table) {
            $table->dropColumn('tahun_pelajaran');
        });
    }
}
