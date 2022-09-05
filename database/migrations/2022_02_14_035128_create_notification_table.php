<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id');
            $table->string('sender_name');
            $table->integer('receiver_id');
            $table->string('reciver_name');
            $table->string('sender_application_name');
            $table->string('title_notificatin');
            $table->string('content_notification');
            $table->string('description_notification');
            $table->boolean('readed_notification');
            $table->string('path_destination',250);
            $table->string('type',250);
            $table->dateTime('readed_at')->nullable();
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
        Schema::dropIfExists('notification');
    }
}
