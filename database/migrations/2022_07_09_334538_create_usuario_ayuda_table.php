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
        Schema::create('usuario_ayuda', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')->nullable()->constrained('users');

            $table->string('motivo')->nullable();
            $table->text('detalle')->nullable();
            $table->string('contacto')->nullable();
            $table->text('info_soporte')->nullable();
            $table->text('msg_to_user')->nullable();

            $table->string('estado')->nullable();

            // $table->boolean('active')->nullable()->default(true);

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
        Schema::dropIfExists('usuario_ayuda');
    }
};
