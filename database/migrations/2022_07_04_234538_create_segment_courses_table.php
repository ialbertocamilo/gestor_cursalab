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
        Schema::create('segment_courses', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('course_id')->nullable();
            $table->string('course_name')->nullable();
            // $table->text('')->nullable();
            $table->boolean('mandatory')->nullable()->default(true);

            $table->foreignId('segment_id')->nullable();
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
        Schema::dropIfExists('segment_courses');
    }
};
