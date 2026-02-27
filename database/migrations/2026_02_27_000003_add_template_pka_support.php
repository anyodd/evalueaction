<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Make st_id nullable on program_kerja (templates have no ST)
        Schema::table('program_kerja', function (Blueprint $table) {
            $table->unsignedBigInteger('st_id')->nullable()->change();
        });

        // 2. Add from_template flag to pk_langkah
        Schema::table('pk_langkah', function (Blueprint $table) {
            $table->boolean('from_template')->default(false)->after('ref_dokumen');
        });
    }

    public function down()
    {
        Schema::table('pk_langkah', function (Blueprint $table) {
            $table->dropColumn('from_template');
        });

        Schema::table('program_kerja', function (Blueprint $table) {
            $table->unsignedBigInteger('st_id')->nullable(false)->change();
        });
    }
};
