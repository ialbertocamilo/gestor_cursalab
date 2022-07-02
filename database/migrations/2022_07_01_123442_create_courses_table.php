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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion');

            $table->string('imagen');
            $table->string('plantilla_diploma');

            $table->string('modalidad');
            $table->string('c_evaluable');

            $table->unsignedBigInteger('requisito_id');
            $table->unsignedBigInteger('duplicado_id');

            $table->tinyInteger('orden');

            $table->text('reinicios_programados');

            $table->tinyInteger('estado')->nullable()->default(1);

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
        Schema::dropIfExists('courses');
    }
};
