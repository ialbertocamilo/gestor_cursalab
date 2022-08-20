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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');

            $table->text('contenido')->nullable();

            $table->string('imagen')->nullable();
            $table->string('archivo')->nullable();

            $table->string('destino')->nullable();
            $table->string('link')->nullable();

            $table->tinyInteger('position')->nullable(); // ???

            $table->boolean('active')->nullable()->default(true);

            $table->dateTime('publish_date')->nullable();

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
        Schema::dropIfExists('announcements');
    }
};
