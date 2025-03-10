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
        Schema::create('summary_topics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('topic_id')->nullable()->constrained('topics');

            $table->unsignedInteger('attempts')->nullable();
            $table->unsignedInteger('correct_answers')->nullable();
            $table->unsignedInteger('failed_answers')->nullable();

            $table->unsignedInteger('views')->nullable();
            $table->unsignedInteger('downloads')->nullable();
            $table->unsignedInteger('restarts')->nullable();

            $table->unsignedDecimal('grade', 8, 2)->nullable();
            $table->boolean('passed')->nullable();

            // $table->tinyInteger('historical')->nullable(); // ????

            // $table->timestamp('test_attempt_at')->nullable();
            $table->json('answers')->nullable();
            $table->timestamp('last_time_evaluated_at')->nullable();

            $table->timestamp('current_quiz_started_at')->nullable();
            $table->timestamp('current_quiz_finishes_at')->nullable();
            $table->boolean('taking_quiz')->nullable();

            $table->foreignId('restarter_id')->nullable()->constrained('users');

            $table->foreignId('source_id')->nullable()->constrained('taxonomies');
            $table->foreignId('status_id')->nullable()->constrained('taxonomies');

            $table->foreignId('last_media_access')->nullable()->constrained('media_topics');
            $table->string('last_media_duration')->nullable();
            $table->json('media_progress')->nullable();

            $table->boolean('active')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('summary_topics');
    }
};
