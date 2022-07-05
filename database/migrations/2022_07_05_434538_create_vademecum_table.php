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
        Schema::create('vademecum', function (Blueprint $table) {
            $table->id();
            
            $table->string('name')->nullable();

            $table->foreignId('media_id')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('taxonomies');
            $table->foreignId('subcategory_id')->nullable()->constrained('taxonomies');

            $table->boolean('active')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vademecum');
    }
};
