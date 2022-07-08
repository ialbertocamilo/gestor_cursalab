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
    //     Schema::create('records', function (Blueprint $table) {
    //         $table->id();

    //         $table->foreignId('topic_id');
    //         $table->foreignId('user_id');

    //         $table->unsignedInteger('attempts')->nullable();
    //         $table->unsignedInteger('correct_answers')->nullable();
    //         $table->unsignedInteger('failed_answers')->nullable();
    //         $table->decimal('grade', 8, 2)->nullable();

    //         $table->timestamp('last_time_evaluated_at')->nullable();

    //         $table->json('answers')->nullable();
    //         $table->foreignId('source_id')->nullable()->constrained('taxonomies');

    //         $table->boolean('approved')->nullable()->default(true);

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
    //     Schema::dropIfExists('records');
    // }
};
