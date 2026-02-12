<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStPersonelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('st_personel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('st_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role_dalam_tim'); // Korwas, Dalnis, Ketua Tim, Anggota
            $table->timestamps();

            $table->foreign('st_id')->references('id')->on('surat_tugas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('st_personel');
    }
}
