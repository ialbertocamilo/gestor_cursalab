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
        Schema::create('summary_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->foreignId('status_id')->nullable()->constrained('taxonomies');

            // $table->tinyInteger('libre')->nullable()->default(false);

            $table->unsignedInteger('completed')->nullable();
            $table->unsignedInteger('assigned')->nullable();
            $table->unsignedInteger('passed')->nullable();
            $table->unsignedInteger('taken')->nullable();
            $table->unsignedInteger('reviewed')->nullable();
            $table->unsignedInteger('failed')->nullable();

            $table->unsignedDecimal('grade_average', 6, 2)->nullable();
            $table->unsignedDecimal('advanced_percentage', 5, 2)->nullable();

            $table->unsignedInteger('attempts')->nullable();
            $table->unsignedInteger('views')->nullable();

            $table->unsignedInteger('restarts')->nullable();
            $table->foreignId('restarter_id')->nullable()->constrained('users');

            $table->timestamp('last_time_evaluated_at')->nullable();
            $table->timestamp('certification_issued_at')->nullable();

            // $table->boolean('active')->nullable()->default(true);

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
        Schema::dropIfExists('summary_courses');
    }
};
