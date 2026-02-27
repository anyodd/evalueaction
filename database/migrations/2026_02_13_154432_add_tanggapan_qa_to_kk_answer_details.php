<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTanggapanQaToKkAnswerDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kk_answer_details', function (Blueprint $table) {
            $table->text('tanggapan_qa')->nullable()->after('catatan_qa');
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
            $table->dropColumn('tanggapan_qa');
        });
    }
}
