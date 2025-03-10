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
            $table->foreignId('subworkspace_id')->nullable()->constrained('workspaces');

            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('code')->nullable();

            $table->string('document')->nullable();

            $table->string('password');

            $table->boolean('active')->nullable()->default(true);

            $table->string('token_firebase')->nullable();

            $table->string('v_android')->nullable();
            $table->string('v_ios')->nullable();
            $table->string('android')->default(0);
            $table->string('ios')->default(0);
            $table->string('huawei')->default(0);
            $table->string('windows')->default(0);

            $table->dateTime('last_login')->nullable();


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
