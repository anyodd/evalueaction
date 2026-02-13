<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateIndicatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_indicators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('uraian');
            $table->enum('tipe', ['header', 'input_text', 'input_number', 'score_manual', 'score_reference']);
            $table->decimal('bobot', 5, 2)->default(0);
            $table->unsignedBigInteger('ref_jenis_id')->nullable();
            $table->timestamps();
            
            $table->foreign('template_id')->references('id')->on('kk_templates')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('template_indicators')->onDelete('cascade');
            $table->foreign('ref_jenis_id')->references('id')->on('jenis_penugasan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_indicators');
    }
}
