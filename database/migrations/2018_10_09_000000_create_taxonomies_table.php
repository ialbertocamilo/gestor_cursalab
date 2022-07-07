<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxonomiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->nullable()->index();
            $table->unsignedBigInteger('external_id_es')->nullable()->index();

            $table->string('group', 30)->nullable();
            $table->string('type', 30)->nullable();
            $table->tinyInteger('position')->nullable();
            $table->string('code', 100)->nullable();
            $table->string('name')->nullable();
            $table->string('path')->nullable();
            $table->string('alias')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('active')->nullable();
            $table->string('description', 3000)->nullable();
            $table->text('detail')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('taxonomies');
            $table->unsignedBigInteger('external_parent_id')->nullable();
            $table->unsignedBigInteger('external_parent_id_es')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('group');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taxonomies');
    }
}
