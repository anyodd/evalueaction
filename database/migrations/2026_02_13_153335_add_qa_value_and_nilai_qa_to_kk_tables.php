<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQaValueAndNilaiQaToKkTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kk_answer_details', function (Blueprint $table) {
            $table->enum('qa_value', ['full', 'partial', 'none'])->nullable()->after('answer_value');
        });

        Schema::table('kk_answers', function (Blueprint $table) {
            $table->decimal('nilai_qa', 5, 2)->nullable()->after('nilai');
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
            $table->dropColumn('qa_value');
        });

        Schema::table('kk_answers', function (Blueprint $table) {
            $table->dropColumn('nilai_qa');
        });
    }
}
