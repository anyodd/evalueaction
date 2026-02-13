<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTemplateIndicatorsEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_indicators', function (Blueprint $table) {
            // Raw statement because doctrine/dbal has issues with ENUMs sometimes
             DB::statement("ALTER TABLE template_indicators MODIFY COLUMN tipe ENUM('header', 'input_text', 'input_number', 'score_manual', 'score_reference', 'criteria_tally') NOT NULL DEFAULT 'score_manual'");
        });
    }

    public function down()
    {
        Schema::table('template_indicators', function (Blueprint $table) {
             DB::statement("ALTER TABLE template_indicators MODIFY COLUMN tipe ENUM('header', 'input_text', 'input_number', 'score_manual', 'score_reference') NOT NULL DEFAULT 'score_manual'");
        });
    }
}
