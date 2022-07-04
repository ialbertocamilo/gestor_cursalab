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
        Schema::create('summary_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->integer('cur_asignados');
            $table->integer('tot_completados');
            $table->integer('intentos');
            $table->integer('porcentaje');

            $table->decimal('nota_prom', 4, 2);
            $table->decimal('rank', 4, 2);

            $table->timestamp('last_ev')->nullable();

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
        Schema::dropIfExists('summary_users');
    }
};
