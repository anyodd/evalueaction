<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyKertasKerjaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kertas_kerja', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable()->after('st_id');
            $table->decimal('nilai_akhir', 5, 2)->nullable()->after('status_approval');
            $table->foreign('template_id')->references('id')->on('kk_templates')->onDelete('cascade');
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
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'nilai_akhir']);
        });
    }
}
