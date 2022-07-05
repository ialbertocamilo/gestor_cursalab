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
        Schema::create('checklist_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->nullable()->constrained('checklists');
            $table->foreignId('entrenador_id')->nullable()->constrained('users');
            $table->foreignId('alumno_id')->nullable()->constrained('users');
            $table->foreignId('curso_id')->nullable()->constrained('courses');
            $table->foreignId('categoria_id')->nullable()->constrained('schools');

            $table->decimal('porcentaje', 4,2)->nullable();

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
        Schema::dropIfExists('checklist_answers');
    }
};
