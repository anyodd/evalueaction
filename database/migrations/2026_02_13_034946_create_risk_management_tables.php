<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskManagementTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_criteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indicator_id');
            $table->text('uraian');
            $table->integer('level')->default(0)->comment('Optional level mapping');
            $table->timestamps();

            $table->foreign('indicator_id')->references('id')->on('template_indicators')->onDelete('cascade');
        });

        Schema::create('kk_answer_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kk_answer_id');
            $table->unsignedBigInteger('criteria_id');
            $table->boolean('is_checked')->default(false);
            $table->timestamps();

            $table->foreign('kk_answer_id')->references('id')->on('kk_answers')->onDelete('cascade');
            $table->foreign('criteria_id')->references('id')->on('template_criteria')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kk_answer_details');
        Schema::dropIfExists('template_criteria');
    }
}
