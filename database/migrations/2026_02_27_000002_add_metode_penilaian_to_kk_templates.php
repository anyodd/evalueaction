<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetodePenilaianToKkTemplates extends Migration
{
    public function up()
    {
        Schema::table('kk_templates', function (Blueprint $table) {
            $table->enum('metode_penilaian', [
                'tally',                // Ya/Sebagian/Tidak → persentase (metode lama)
                'building_block',       // Level 1-5 sekuensial (Ya/Tidak saja)
                'criteria_fulfillment'  // Level 1-5 independen (Ya/Sebagian/Tidak)
            ])->default('tally')->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('kk_templates', function (Blueprint $table) {
            $table->dropColumn('metode_penilaian');
        });
    }
}
