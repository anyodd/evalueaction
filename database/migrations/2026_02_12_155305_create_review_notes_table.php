<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kk_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->text('catatan');
            $table->string('status'); // Perbaikan, OK
            $table->timestamps();

            $table->foreign('kk_id')->references('id')->on('kertas_kerja')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_notes');
    }
}
