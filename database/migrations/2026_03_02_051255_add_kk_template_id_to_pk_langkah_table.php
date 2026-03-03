<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKkTemplateIdToPkLangkahTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pk_langkah', function (Blueprint $table) {
            $table->unsignedBigInteger('kk_template_id')->nullable()->after('kertas_kerja_id');
            $table->foreign('kk_template_id')->references('id')->on('kk_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pk_langkah', function (Blueprint $table) {
            $table->dropForeign(['kk_template_id']);
            $table->dropColumn('kk_template_id');
        });
    }
}
