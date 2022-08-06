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
        Schema::create('criterion_workspace', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->constrained('workspaces');
            $table->foreignId('criterion_id')->nullable()->constrained('criteria');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('criterion_workspace');
    }
};
