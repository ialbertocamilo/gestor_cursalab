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
            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->tinyInteger('libre')->nullable()->default(false);

            $table->integer('asignados')->nullable();
            $table->integer('aprobados')->nullable();
            $table->integer('realizados')->nullable();
            $table->integer('revisados')->nullable();
            $table->integer('desaprobados')->nullable();

            $table->decimal('nota_prom', 4, 2)->nullable();
            $table->decimal('porcentaje', 4, 2)->nullable();

            $table->integer('intentos')->nullable();
            $table->integer('visitas')->nullable();

            $table->timestamp('last_ev')->nullable();

            $table->boolean('active')->nullable()->default(true);

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
