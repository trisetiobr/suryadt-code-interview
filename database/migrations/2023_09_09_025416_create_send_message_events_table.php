<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendMessageEventsTable extends Migration
{
    public function up()
    {
        Schema::create('send_message_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('status');
            $table->string('event_type');
            $table->string('errors')->nullable();
            $table->string('timezone');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('send_message_events');
    }
}
