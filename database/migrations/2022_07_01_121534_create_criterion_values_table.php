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
        Schema::create('criterion_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('criterion_id')->nullable()->constrained('criteria');

            $table->string('name_text')->nullable();
            $table->date('name_date')->nullable();
            $table->dateTime('name_datetime')->nullable();
            $table->tinyInteger('name_boolean')->nullable()->default(0);
            $table->decimal('name_decimal', 10, 2)->nullable();
            $table->integer('name_integer')->nullable();

            $table->foreignId('parent_id')->nullable()->constrained('criterion_values');

            $table->tinyInteger('position')->nullable();

            $table->tinyInteger('exclusive_criterion_id')->nullable();

            $table->tinyInteger('active')->nullable()->default(1);
            $table->string('description', 3000)->nullable();


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
        Schema::dropIfExists('criterion_values');
    }
};
