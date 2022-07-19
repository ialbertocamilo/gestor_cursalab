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
        Schema::create('block_segment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->nullable()->constrained('blocks');
            $table->foreignId('segment_id')->nullable()->constrained('segments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('block_segment');
    }
};
