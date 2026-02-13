<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQaFieldsToKertasKerjaTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kertas_kerja', function (Blueprint $table) {
            $table->decimal('nilai_akhir_qa', 8, 2)->nullable()->after('nilai_akhir');
        });

        Schema::table('kk_answer_details', function (Blueprint $table) {
            $table->decimal('score_qa', 8, 2)->nullable()->after('score');
            $table->text('catatan_qa')->nullable()->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kk_answer_details', function (Blueprint $table) {
            $table->dropColumn(['score_qa', 'catatan_qa']);
        });

        Schema::table('kertas_kerja', function (Blueprint $table) {
            $table->dropColumn('nilai_akhir_qa');
        });
    }
}
