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
        Schema::create('criteria_workspace', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->constrained('workspaces');
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
        Schema::dropIfExists('criteria_workspace');
    }
};
