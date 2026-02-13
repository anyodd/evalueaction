<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvidenceAndNotesToKkAnswerDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kk_answer_details', function (Blueprint $table) {
            $table->text('catatan')->nullable();
            $table->string('evidence_file')->nullable();
            $table->string('evidence_link')->nullable();
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
            $table->dropColumn(['catatan', 'evidence_file', 'evidence_link']);
        });
    }
}
