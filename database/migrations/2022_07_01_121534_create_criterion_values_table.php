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
            $table->unsignedBigInteger('external_id')->nullable()->index();

            $table->foreignId('criterion_id')->nullable()->constrained('criteria');

            $table->string('value_text')->nullable();
            $table->date('value_date')->nullable();
            $table->dateTime('value_datetime')->nullable();
            $table->boolean('value_boolean')->nullable()->default(false);
            $table->decimal('value_decimal', 10, 2)->nullable();
            $table->integer('value_integer')->nullable();

            $table->foreignId('parent_id')->nullable()->constrained('criterion_values');

            $table->tinyInteger('position')->nullable();

            $table->foreignId('exclusive_criterion_id')->nullable()->constrained('criteria');

            $table->boolean('active')->nullable()->default(true);
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
