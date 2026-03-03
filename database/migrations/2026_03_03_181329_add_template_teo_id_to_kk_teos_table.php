<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateTeoIdToKkTeosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kk_teos', function (Blueprint $table) {
            $table->foreignId('template_teo_id')->nullable()->after('kk_answer_id')->constrained('template_teos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kk_teos', function (Blueprint $table) {
            $table->dropForeign(['template_teo_id']);
            $table->dropColumn('template_teo_id');
        });
    }
}
