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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('key')->nullable();
            $table->string('secret')->nullable();
            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('identifier')->nullable();

            $table->text('sdk_token')->nullable();
            $table->text('zak_token')->nullable();

            $table->unsignedBigInteger('service_id')->nullable()->index();
            
            $table->unsignedBigInteger('service_id')->nullable()->index();
            $table->unsignedBigInteger('plan_id')->nullable()->index();
            $table->unsignedBigInteger('type_id')->nullable()->index();
            // $table->morphs('model');

            $table->text('description')->nullable();
            $table->boolean('active')->nullable();

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
        Schema::drop('accounts');
    }
}
