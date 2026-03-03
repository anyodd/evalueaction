<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateIndicatorIdToPkLangkahTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pk_langkah', function (Blueprint $table) {
            $table->unsignedBigInteger('template_indicator_id')->nullable()->after('kk_template_id');
            
            // Foreign key to map to Level 3 Indicator (Parameter Penilaian)
            $table->foreign('template_indicator_id')->references('id')->on('template_indicators')->onDelete('set null');
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
            $table->dropForeign(['template_indicator_id']);
            $table->dropColumn('template_indicator_id');
        });
    }
}
