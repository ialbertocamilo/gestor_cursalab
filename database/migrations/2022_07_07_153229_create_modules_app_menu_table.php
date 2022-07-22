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
        Schema::create('workspace_app_menu', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->constrained('workspaces');
            $table->foreignId('menu_id')->nullable()->constrained('taxonomies');

            $table->tinyInteger('position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules_app_menu');
    }
};
