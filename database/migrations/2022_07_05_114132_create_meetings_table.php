<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {

            $table->id();

            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->boolean('embed')->nullable();

            $table->morphs('model');

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('finishes_at')->nullable();

            $table->tinyInteger('duration')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();


            $table->string('url', 3000)->nullable();
            $table->string('url_start', 3000)->nullable();

            $table->string('identifier')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();

            $table->dateTime('attendance_call_first_at')->nullable();
            $table->dateTime('attendance_call_middle_at')->nullable();
            $table->dateTime('attendance_call_last_at')->nullable();

            $table->timestamp('url_start_generated_at')->nullable();
            $table->timestamp('report_generated_at')->nullable();

            $table->foreignId('status_id')->nullable()->index()->constrained('taxonomies');
            $table->foreignId('type_id')->nullable()->index()->constrained('taxonomies');
            $table->foreignId('account_id')->nullable()->index()->constrained('accounts');
            $table->foreignId('host_id')->nullable()->index()->constrained('users');
            $table->foreignId('user_id')->nullable()->index()->constrained('users');

            $table->text('raw_data_response')->nullable();

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
        Schema::drop('meetings');
    }
}
