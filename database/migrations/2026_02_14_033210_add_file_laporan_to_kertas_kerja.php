<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileLaporanToKertasKerja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kertas_kerja', function (Blueprint $table) {
            $table->string('file_laporan')->nullable()->after('status_qa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kertas_kerja', function (Blueprint $table) {
            $table->dropColumn('file_laporan');
        });
    }
}
