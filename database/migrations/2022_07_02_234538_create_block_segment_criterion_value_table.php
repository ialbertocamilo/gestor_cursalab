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
        Schema::create('block_segment_criterion_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_segment_id')->nullable()->constrained('block_segment');
            $table->foreignId('criterion_value_id')->nullable()->constrained('criterion_values');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('block_segment_criterion_value');
    }
};
