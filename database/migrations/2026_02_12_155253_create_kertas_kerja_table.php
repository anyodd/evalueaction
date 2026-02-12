<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKertasKerjaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kertas_kerja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('st_id');
            $table->unsignedBigInteger('user_id'); // Anggota yang membuat KK
            $table->string('judul_kk');
            $table->longText('isi_kk')->nullable();
            $table->string('status_approval')->default('Draft'); // Draft, Review Ketua, Review Dalnis, Review Korwas, Final
            $table->string('file_pendukung')->nullable();
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
        Schema::dropIfExists('kertas_kerja');
    }
}
