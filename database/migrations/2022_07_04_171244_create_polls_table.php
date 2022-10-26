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
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->nullable();
            $table->foreignId('workspace_id')->nullable()->constrained('workspaces');
            $table->foreignId('type_id')->nullable()->constrained('taxonomies');

            $table->boolean('anonima')->nullable()->default(false);

            $table->string('titulo');
            $table->string('imagen');

            $table->tinyInteger('position')->nullable();

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
        Schema::dropIfExists('polls');
    }
};
