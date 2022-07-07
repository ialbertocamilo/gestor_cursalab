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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->nullable()->index();

            $table->string('name');
            $table->string('lastname')->nullable();
            $table->string('surname')->nullable();

            $table->foreignId('type_id')->nullable()->constrained('taxonomies');
            $table->foreignId('workspace_id')->nullable()->constrained('workspaces');

            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('code')->nullable();

            $table->string('password');

            $table->boolean('active')->nullable()->default(true);

            $table->rememberToken();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
