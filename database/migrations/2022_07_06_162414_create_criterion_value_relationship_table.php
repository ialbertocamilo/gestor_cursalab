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
        Schema::create('criterion_value_relationship', function (Blueprint $table) {
            $table->foreignId('criterion_value_parent_id')->nullable()->constrained('criterion_values');
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
        Schema::dropIfExists('criterion_value_relationship');
    }
};
