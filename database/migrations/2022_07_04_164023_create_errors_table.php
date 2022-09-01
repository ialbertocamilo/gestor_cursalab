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
        Schema::create('errors', function (Blueprint $table) {
            $table->id();

            $table->string('message', 350)->nullable();
              
            $table->string('file')->nullable();
            $table->string('file_path')->nullable();
              
            $table->unsignedTinyInteger('line')->nullable();
              
            $table->text('stack_trace')->nullable();
              
            $table->char('code', 50)->nullable();
            $table->char('ip', 20)->nullable();

            $table->string('url')->nullable();

            $table->foreignId('status_id')->nullable()->constrained('taxonomies');
            $table->foreignId('user_id')->nullable()->constrained('users');

              // `browser_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              // `platform_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,

            $table->foreignId('platform_id')->nullable()->constrained('taxonomies');
            $table->foreignId('browser_family_id')->nullable()->constrained('taxonomies');
            $table->foreignId('browser_version_id')->nullable()->constrained('taxonomies');
            $table->foreignId('device_family_id')->nullable()->constrained('taxonomies');
            $table->foreignId('device_model_id')->nullable()->constrained('taxonomies');
            $table->foreignId('platform_family_id')->nullable()->constrained('taxonomies');
            $table->foreignId('platform_version_id')->nullable()->constrained('taxonomies');

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
        Schema::dropIfExists('errors');
    }
};
