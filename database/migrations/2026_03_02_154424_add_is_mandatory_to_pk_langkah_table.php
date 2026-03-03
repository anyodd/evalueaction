<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMandatoryToPkLangkahTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pk_langkah', function (Blueprint $table) {
            $table->boolean('is_mandatory')->default(false)->after('from_template');
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
            $table->dropColumn('is_mandatory');
        });
    }
}
