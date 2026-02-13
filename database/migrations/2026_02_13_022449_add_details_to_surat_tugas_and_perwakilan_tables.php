<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToSuratTugasAndPerwakilanTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->date('tgl_mulai')->nullable()->after('tgl_st');
            $table->date('tgl_selesai')->nullable()->after('tgl_mulai');
        });

        Schema::table('perwakilan', function (Blueprint $table) {
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
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
            $table->dropColumn(['tgl_mulai', 'tgl_selesai']);
        });

        Schema::table('perwakilan', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'telepon', 'email', 'website']);
        });
    }
}
