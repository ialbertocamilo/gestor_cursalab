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
        Schema::create('modules_app_menu', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->constrained('criterion_values');
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
