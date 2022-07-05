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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();

            $table->text('titulo')->nullable();
            $table->text('texto')->nullable();

            $table->foreignId('creador_id')->nullable()->constrained('users');
            $table->text('destinatarios')->nullable();

            $table->unsignedInteger('success')->nullable();
            $table->unsignedInteger('failure')->nullable();
            $table->text('detalles_json')->nullable();

            $table->unsignedInteger('estado_envio')->nullable();

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
        Schema::dropIfExists('push_notifications');
    }
};
