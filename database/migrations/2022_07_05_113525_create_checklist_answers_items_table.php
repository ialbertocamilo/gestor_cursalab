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
        Schema::create('checklist_answers_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_answer_id')->nullable()->constrained('checklist_answers');
            $table->foreignId('checklist_item_id')->nullable()->constrained('checklist_items');

            $table->char('calificacion', 30);

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
        Schema::dropIfExists('checklist_answers_items');
    }
};
