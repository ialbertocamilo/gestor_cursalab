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
            $table->foreignId('module_id')->nullable()->constrained('taxonomies');

            $table->string('nombre');

            $table->text('contenido');

            $table->string('imagen');
            $table->string('archivo');

            $table->string('destino');
            $table->string('link');

            $table->tinyInteger('position'); // ???

            $table->boolean('active')->nullable()->default(true);

            $table->timestamp('publish_date');

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
