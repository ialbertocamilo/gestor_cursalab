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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->nullable()->index();

            $table->string('name');
            $table->string('code')->nullable();
            $table->string('description')->nullable();

            $table->string('imagen')->nullable();

            // $table->string('modalidad');

            $table->string('plantilla_diploma')->nullable();

            $table->tinyInteger('position')->nullable();

            $table->json('scheduled_restarts')->nullable();

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
        Schema::dropIfExists('schools');
    }
};
