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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('courses');

            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('imagen')->nullable();
            $table->tinyInteger('position')->nullable();

            $table->integer('visits_count'); // visitas

            $table->boolean('assessable')->nullable()->default(false);

            $table->foreignId('topic_requirement_id')->nullable()->constrained('topics');
            // por confirmar
            $table->foreignId('type_evaluation_id')->nullable()->constrained('taxonomies');
            $table->foreignId('duplicate_id')->nullable()->constrained('topics');

            $table->boolean('active')->nullable()->default(true);

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
        Schema::dropIfExists('topics');
    }
};
