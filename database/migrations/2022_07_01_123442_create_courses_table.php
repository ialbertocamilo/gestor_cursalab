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
            $table->unsignedBigInteger('external_id')->nullable()->index();

            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('external_code')->nullable();
            $table->string('description')->nullable();

            $table->string('imagen')->nullable();
            $table->string('plantilla_diploma')->nullable();

            // $table->string('modalidad');
            $table->boolean('freely_eligible')->nullable()->default(false);
            $table->boolean('assessable')->nullable()->default(false);

            $table->decimal('duration', 6,2)->nullable();
            $table->decimal('investment', 10,2)->nullable();

            // $table->unsignedBigInteger('requisito_id');
            // $table->unsignedBigInteger('duplicado_id');

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
        Schema::dropIfExists('courses');
    }
};
