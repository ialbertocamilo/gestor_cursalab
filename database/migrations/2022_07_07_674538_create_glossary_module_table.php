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
        Schema::create('glossary_module', function (Blueprint $table) {

            $table->foreignId('glossary_id')->nullable()->constrained('glossaries');
            $table->foreignId('module_id')->nullable()->constrained('criterion_values');

            $table->string('code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('glossary_module');
    }
};
