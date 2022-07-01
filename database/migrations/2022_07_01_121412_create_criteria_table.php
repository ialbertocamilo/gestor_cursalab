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

            $table->tinyInteger('show_as_parent')->nullable()->default(0);

            $table->tinyInteger('show_in_reports')->nullable()->default(0);
            $table->tinyInteger('show_in_ranking')->nullable()->default(0);
            $table->tinyInteger('show_in_profile')->nullable()->default(0);
            $table->tinyInteger('show_in_segmentation')->nullable()->default(0);
            $table->tinyInteger('show_in_form')->nullable()->default(0);

            $table->tinyInteger('required')->nullable()->default(0);

            $table->tinyInteger('editable_configuration')->nullable()->default(0);
            $table->tinyInteger('editable_segmentation')->nullable()->default(0);

            $table->tinyInteger('multiple')->nullable()->default(0);

            $table->tinyInteger('active')->nullable()->default(1);
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
