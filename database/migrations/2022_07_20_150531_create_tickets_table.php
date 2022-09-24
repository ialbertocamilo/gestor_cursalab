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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->nullable()->index();
            $table->foreignId('workspace_id')->nullable()->constrained('workspaces');
            $table->foreignId('user_id')->nullable()->index()->constrained('users');
            $table->string('reason')->nullable();
            $table->string('detail')->nullable();
            $table->string('dni')->nullable();
            $table->string('name')->nullable();
            $table->string('contact')->nullable();
            $table->string('info_support')->nullable();
            $table->string('msg_to_user')->nullable();
            $table->string('status')->nullable();

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
        Schema::dropIfExists('tickets');
    }
};
