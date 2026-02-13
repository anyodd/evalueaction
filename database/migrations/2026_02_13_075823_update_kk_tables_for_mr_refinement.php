<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateKkTablesForMrRefinement extends Migration
{
    public function up()
    {
        // 1. Update kk_answer_details for 3-option scoring
        Schema::table('kk_answer_details', function (Blueprint $table) {
            $table->dropColumn('is_checked');
            $table->string('answer_value')->nullable()->default('none')->comment('full, partial, none'); 
            $table->decimal('score', 5, 2)->default(0.00);
        });

        // 2. Update kk_answers for Evidence (GDrive)
        Schema::table('kk_answers', function (Blueprint $table) {
            $table->string('evidence_file')->nullable()->comment('Relative path or GDrive ID');
            $table->string('evidence_link')->nullable()->comment('Manual external link');
        });
        
        // 3. Update template_criteria if needed (add max score?) - Not strictly needed if logic is standardized 1.0/0.5/0
    }

    public function down()
    {
        Schema::table('kk_answer_details', function (Blueprint $table) {
            $table->dropColumn(['answer_value', 'score']);
            $table->boolean('is_checked')->default(false);
        });

        Schema::table('kk_answers', function (Blueprint $table) {
            $table->dropColumn(['evidence_file', 'evidence_link']);
        });
    }
}
