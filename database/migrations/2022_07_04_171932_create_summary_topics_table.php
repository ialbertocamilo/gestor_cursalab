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

            $table->integer('total_attempts')->nullable();
            $table->integer('total_answers_right')->nullable();
            $table->integer('total_answers_failed')->nullable();

            $table->integer('grade')->nullable();

            $table->tinyInteger('historical')->nullable(); // ????

            $table->timestamp('test_attempt_at')->nullable();

            $table->foreignId('source_id')->nullable()->constrained('taxonomies');
            $table->foreignId('status_id')->nullable()->constrained('taxonomies');

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
