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
        Schema::create('segment_course', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->string('course_name')->nullable();
            // $table->text('')->nullable();
            $table->boolean('mandatory')->nullable()->default(true);

            $table->foreignId('segment_id')->nullable()->constrained('segments');
            $table->tinyInteger('position')->nullable();

            $table->smallInteger('criterion_value_count')->nullable();

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
        Schema::dropIfExists('segment_course');
    }
};
