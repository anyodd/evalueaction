<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKkAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kk_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kertas_kerja_id');
            $table->unsignedBigInteger('indikator_id');
            $table->text('nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('ref_st_id')->nullable();
            $table->timestamps();
            
            $table->foreign('kertas_kerja_id')->references('id')->on('kertas_kerja')->onDelete('cascade');
            $table->foreign('indikator_id')->references('id')->on('template_indicators')->onDelete('cascade');
            $table->foreign('ref_st_id')->references('id')->on('surat_tugas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kk_answers');
    }
}
