<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramKerjaTables extends Migration
{
    public function up()
    {
        // 1. Program Kerja (Header)
        Schema::create('program_kerja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('st_id');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('ruang_lingkup')->nullable();
            $table->text('metodologi')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->timestamps();

            $table->foreign('st_id')->references('id')->on('surat_tugas')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Langkah-langkah Program Kerja
        Schema::create('pk_langkah', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_kerja_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('urutan')->default(0);
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis_prosedur', [
                'wawancara', 'observasi', 'inspeksi_dokumen', 'analisis_data',
                'konfirmasi', 'rekalkulasi', 'lainnya'
            ])->nullable();
            $table->integer('target_hari')->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->unsignedBigInteger('kertas_kerja_id')->nullable();
            $table->text('catatan_hasil')->nullable();
            $table->string('ref_dokumen')->nullable();
            $table->timestamps();

            $table->foreign('program_kerja_id')->references('id')->on('program_kerja')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('pk_langkah')->onDelete('set null');
            $table->foreign('kertas_kerja_id')->references('id')->on('kertas_kerja')->onDelete('set null');
        });

        // 3. Penugasan Langkah ke Personel
        Schema::create('pk_assignment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pk_langkah_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assigned_by');
            $table->text('catatan')->nullable();
            $table->enum('status', ['assigned', 'accepted', 'in_progress', 'completed'])->default('assigned');
            $table->date('tgl_deadline')->nullable();
            $table->timestamps();

            $table->foreign('pk_langkah_id')->references('id')->on('pk_langkah')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pk_assignment');
        Schema::dropIfExists('pk_langkah');
        Schema::dropIfExists('program_kerja');
    }
}
