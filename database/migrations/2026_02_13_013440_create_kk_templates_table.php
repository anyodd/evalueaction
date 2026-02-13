<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKkTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kk_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jenis_penugasan_id');
            $table->string('nama');
            $table->integer('tahun');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('jenis_penugasan_id')->references('id')->on('jenis_penugasan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kk_templates');
    }
}
