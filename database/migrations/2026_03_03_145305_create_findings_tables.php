<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFindingsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_findings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indicator_id');
            $table->text('teo')->nullable();
            $table->text('kriteria')->nullable();
            $table->text('penyebab')->nullable();
            $table->text('akibat')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->timestamps();

            $table->foreign('indicator_id')->references('id')->on('template_indicators')->onDelete('cascade');
        });

        Schema::create('kk_findings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kk_answer_id');
            $table->unsignedBigInteger('template_finding_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->text('teo')->nullable();
            $table->text('kriteria')->nullable();
            $table->text('penyebab')->nullable();
            $table->text('akibat')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->timestamps();

            $table->foreign('kk_answer_id')->references('id')->on('kk_answers')->onDelete('cascade');
            $table->foreign('template_finding_id')->references('id')->on('template_findings')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kk_findings');
        Schema::dropIfExists('template_findings');
    }
}
