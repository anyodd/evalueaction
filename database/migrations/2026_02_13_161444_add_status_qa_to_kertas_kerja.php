<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusQaToKertasKerja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kertas_kerja', function (Blueprint $table) {
            $table->enum('status_qa', ['Draft', 'Final'])->default('Draft')->after('nilai_akhir_qa');
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
            $table->dropColumn('status_qa');
        });
    }
}
