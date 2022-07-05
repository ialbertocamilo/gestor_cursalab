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
            $table->string('title');
            $table->string('description');

            $table->foreignId('category_id')->nullable()->constrained('taxonomies');

            $table->string('media_video');
            $table->string('media_type');

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
