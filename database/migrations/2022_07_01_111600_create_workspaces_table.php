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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterion_value_id')->nullable()->constrained('criterion_values');

            $table->string('name');
            $table->string('slug');
            $table->string('description')->nullable();

            // columnas de modulo
            $table->string('logo')->nullable();
            $table->string('plantilla_diploma')->nullable();

            $table->string('codigo_matricula')->nullable();
            $table->string('mod_evaluaciones')->nullable();
            $table->string('reinicios_programado')->nullable();
            // columnas de modulo

            $table->foreignId('parent_id')->nullable()->constrained('workspaces');

            $table->boolean('active')->nullable()->default(true);

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
        Schema::dropIfExists('workspaces');
    }
};
