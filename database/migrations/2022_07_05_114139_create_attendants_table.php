<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendants', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meeting_id')->nullable()->index()->constrained('meetings');
            $table->foreignId('usuario_id')->nullable()->index()->constrained('users');

            $table->string('link')->nullable();

            $table->unsignedTinyInteger('total_logins')->nullable();
            $table->unsignedTinyInteger('total_logouts')->nullable();
            $table->unsignedTinyInteger('total_duration')->nullable();

            $table->timestamp('first_login_at')->nullable();
            $table->timestamp('first_logout_at')->nullable();

            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_logout_at')->nullable();

            $table->boolean('present_at_first_call')->nullable();
            $table->boolean('present_at_middle_call')->nullable();
            $table->boolean('present_at_last_call')->nullable();

            $table->timestamp('confirmed_attendance_at')->nullable();

            $table->foreignId('type_id')->nullable()->index()->constrained('taxonomies');
            $table->boolean('online')->nullable();

            $table->timestamp('first_attempt_at')->nullable();

            $table->string('identifier')->nullable();
            $table->string('ip')->nullable();

            $table->foreignId('browser_family_id')->nullable()->index()->constrained('taxonomies');
            $table->foreignId('browser_version_id')->nullable()->index()->constrained('taxonomies');
            $table->foreignId('platform_family_id')->nullable()->index()->constrained('taxonomies');
            $table->foreignId('platform_version_id')->nullable()->index()->constrained('taxonomies');
            $table->foreignId('device_family_id')->nullable()->index()->constrained('taxonomies');
            $table->foreignId('device_model_id')->nullable()->index()->constrained('taxonomies');

            $table->timestamps();
            $table->softDeletes();

            // $table->index(['meeting_id', 'usuario_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('attendants');
    }
}
