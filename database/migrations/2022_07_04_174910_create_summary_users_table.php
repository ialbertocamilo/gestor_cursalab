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
        Schema::create('summary_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->integer('courses_assigned');
            $table->integer('courses_completed');
            $table->integer('attempts');
            $table->integer('advanced_percentage');

            $table->decimal('grade_average', 4, 2);
            $table->decimal('score', 8, 2);

            $table->timestamp('last_time_evaluated_at')->nullable();

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
        Schema::dropIfExists('summary_users');
    }
};
