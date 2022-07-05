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
    //     Schema::create('certifications', function (Blueprint $table) {

    //         $table->id();

    //         $table->foreignId('user_id')->nullable();
    //         $table->foreignId('school_id')->nullable();
    //         $table->foreignId('course_id')->nullable();
    //         $table->foreignId('topic_id')->nullable();
            
    //         $table->timestamp('issued_at')->nullable();
    //         // $table->timestamp('certification_issued_at')->nullable();
            

    //         $table->timestamps();
    //         $table->softDeletes();
    //     });
    // }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    // public function down()
    // {
    //     Schema::dropIfExists('certifications');
    // }
};
