<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {

            $table->id();

            $table->string('code')->nullable();
            $table->string('title')->nullable();
            $table->string('title_short')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('slug')->nullable();
            $table->text('content')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('position')->nullable();

            $table->foreignId('platform_id')->nullable()->constrained('taxonomies');
            $table->foreignId('section_id')->nullable()->constrained('taxonomies');
            $table->foreignId('category_id')->nullable()->constrained('taxonomies');
            $table->foreignId('user_id')->nullable();

            $table->boolean('active')->nullable()->default(true);
            // $table->boolean('enable_field')->nullable()->default(true);

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
        Schema::dropIfExists('posts');
    }
}
