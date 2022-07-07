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
        Schema::create('videoteca', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->nullable()->index();
            $table->string('title');
            $table->string('description')->nullable();

            $table->foreignId('category_id')->nullable()->constrained('taxonomies');

            $table->string('media_video')->nullable();
            $table->string('media_type')->nullable();

            $table->foreignId('media_id')->nullable()->constrained('media');
            $table->foreignId('preview_id')->nullable()->constrained('media');

            $table->boolean('active')->nullable()->default(true);

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
        Schema::dropIfExists('videoteca');
    }
};
