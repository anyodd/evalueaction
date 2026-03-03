<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop old flat tables
        Schema::dropIfExists('kk_findings');
        Schema::dropIfExists('template_findings');

        // 1. Template TEO (Parent)
        Schema::create('template_teos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('template_indicators')->onDelete('cascade');
            $table->text('teo');
            $table->timestamps();
        });

        // 2. Template Causes
        Schema::create('template_causes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teo_id')->constrained('template_teos')->onDelete('cascade');
            $table->text('uraian');
            $table->timestamps();
        });

        // 3. Template Recommendations
        Schema::create('template_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teo_id')->constrained('template_teos')->onDelete('cascade');
            $table->text('uraian');
            $table->timestamps();
        });

        // 4. Pivot Table for Many-to-Many relationship between Causes and Recommendations
        Schema::create('template_cause_recommendation', function (Blueprint $table) {
            $table->foreignId('cause_id')->constrained('template_causes')->onDelete('cascade');
            $table->foreignId('recommendation_id')->constrained('template_recommendations')->onDelete('cascade');
            $table->primary(['cause_id', 'recommendation_id'], 't_c_r_primary');
        });

        // 5. Transactional TEO Snapshot
        Schema::create('kk_teos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kk_answer_id')->constrained('kk_answers')->onDelete('cascade');
            $table->text('teo');
            $table->timestamps();
        });

        // 6. Transactional Findings Snapshot (links specific cause-recommendation pairs)
        Schema::create('kk_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kk_teo_id')->constrained('kk_teos')->onDelete('cascade');
            $table->text('cause');
            $table->text('recommendation');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kk_findings');
        Schema::dropIfExists('kk_teos');
        Schema::dropIfExists('template_cause_recommendation');
        Schema::dropIfExists('template_recommendations');
        Schema::dropIfExists('template_causes');
        Schema::dropIfExists('template_teos');
    }
};
