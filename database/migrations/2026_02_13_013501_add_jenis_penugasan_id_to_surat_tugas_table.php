<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisPenugasanIdToSuratTugasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_penugasan_id')->nullable()->after('tahun_evaluasi');
            $table->unsignedBigInteger('template_id')->nullable()->after('jenis_penugasan_id');
            $table->foreign('jenis_penugasan_id')->references('id')->on('jenis_penugasan')->onDelete('set null');
            $table->foreign('template_id')->references('id')->on('kk_templates')->onDelete('set null');
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
            $table->dropForeign(['jenis_penugasan_id']);
            $table->dropForeign(['template_id']);
            $table->dropColumn(['jenis_penugasan_id', 'template_id']);
        });
    }
}
