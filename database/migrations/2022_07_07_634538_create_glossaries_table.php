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
        Schema::create('glossaries', function (Blueprint $table) {

            $table->id();

            $table->string('grupo')->nullable();
            $table->string('tipo')->nullable();
            $table->string('name')->nullable();
            $table->boolean('active')->nullable()->default(true);

            $table->foreignId('parent_id')->nullable()->constrained('glossaries');

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
        Schema::dropIfExists('glossaries');
    }
};
