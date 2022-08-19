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
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();

            // $table->string('reference_name')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();

            $table->tinyInteger('position')->nullable();

            $table->foreignId('parent_id')->nullable()->constrained('criteria');
            $table->foreignId('field_id')->nullable()->constrained('taxonomies');
            // $table->unsignedBigInteger('field_id')->nullable()->index();
            $table->foreignId('validation_id')->nullable()->constrained('taxonomies');
            // $table->unsignedBigInteger('validation_id')->nullable()->index();

            $table->boolean('show_as_parent')->nullable()->default(false);

            $table->boolean('show_in_reports')->nullable()->default(false);
            $table->boolean('show_in_ranking')->nullable()->default(false);
            $table->boolean('show_in_profile')->nullable()->default(false);
            $table->boolean('show_in_segmentation')->nullable()->default(false);
            $table->boolean('show_in_form')->nullable()->default(false);
            $table->boolean('is_default')->nullable()->default(false);

            $table->boolean('required')->nullable()->default(false);

            $table->boolean('editable_configuration')->nullable()->default(false);
            $table->boolean('editable_segmentation')->nullable()->default(false);

            $table->boolean('multiple')->nullable()->default(false);

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
        Schema::dropIfExists('criteria');
    }
};
