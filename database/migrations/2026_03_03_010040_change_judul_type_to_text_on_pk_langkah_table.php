<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeJudulTypeToTextOnPkLangkahTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pk_langkah', function (Blueprint $table) {
            $table->text('judul')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pk_langkah', function (Blueprint $table) {
            $table->string('judul', 255)->change();
        });
    }
}
