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
        Schema::create('school_subworkspace', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->constrained('schools');

            $table->foreignId('subworkspace_id')->nullable()->constrained('workspaces');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_subworkspace');
    }
};
