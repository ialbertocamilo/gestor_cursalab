<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segments_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('criterion_id')->nullable()->constrained('criteria');
            $table->foreignId('criterion_value_id')->nullable()->constrained('criterion_values');
            $table->foreignId('type_id')->nullable()->constrained('taxonomies');

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('finishes_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // $table->index(['service_id', 'plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('segments_values');
    }
}
