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
    // public function up()
    // {
    //     Schema::create('restarts', function (Blueprint $table) {

    //         $table->id();

    //         $table->foreignId('user_id')->nullable();
    //         $table->foreignId('course_id')->nullable();
    //         $table->foreignId('topic_id')->nullable();

    //         $table->foreignId('restarter_id')->nullable()->constrained('users');

    //         $table->foreignId('type_id')->nullable()->constrained('taxonomies'); // course, topic, total
            
    //         $table->unsignedInteger('total')->nullable();
    //         // $table->unsignedInteger('total_restarts')->nullable();

    //         $table->timestamps();
    //         $table->softDeletes();
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  *
    //  * @return void
    //  */
    // public function down()
    // {
    //     Schema::dropIfExists('restarts');
    // }
};
