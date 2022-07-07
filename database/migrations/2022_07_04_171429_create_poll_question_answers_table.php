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
        Schema::create('poll_question_answers', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('external_id')->nullable();
            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('poll_question_id')->nullable()->constrained('poll_questions');

            $table->foreignId('type_id')->nullable()->constrained('taxonomies');

            $table->text('respuestas')->nullable();

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
        Schema::dropIfExists('poll_questions');
    }
};
