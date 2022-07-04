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
        Schema::create('course_capabilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->foreignId('type_id')->nullable()->constrained('taxonomies');

            $table->integer('score');

            $table->softDeletes();
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
        Schema::dropIfExists('course_capabilities');
    }
};
