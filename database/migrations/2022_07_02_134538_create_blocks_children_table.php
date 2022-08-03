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
        Schema::create('blocks_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->nullable()->constrained('blocks');
            $table->foreignId('block_child_id')->nullable()->constrained('blocks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blocks_children');
    }
};
