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
        Schema::create('segment_course_criterion', function (Blueprint $table) {
            // $table->id();

            $table->foreignId('segment_course_id')->nullable()->constrained('segment_course');
            $table->foreignId('criterion_value_id')->nullable()->constrained('criterion_values');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('segment_course_criterion');
    }
};
