<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisProsedurToTemplateLangkahsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_langkahs', function (Blueprint $table) {
            $table->string('jenis_prosedur')->nullable()->after('uraian');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('template_langkahs', function (Blueprint $table) {
            $table->dropColumn('jenis_prosedur');
        });
    }
}
