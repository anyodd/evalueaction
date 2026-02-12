<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerwakilanIdToSuratTugasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->unsignedBigInteger('perwakilan_id')->after('admin_id')->nullable();
            $table->foreign('perwakilan_id')->references('id')->on('perwakilan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropForeign(['perwakilan_id']);
            $table->dropColumn('perwakilan_id');
        });
    }
}
