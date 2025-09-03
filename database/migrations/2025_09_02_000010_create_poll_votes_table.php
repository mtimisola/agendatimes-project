<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePollVotesTable extends Migration
{
    public function up()
    {
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('online_poll_id');
            $table->string('ip_address', 45);
            $table->timestamps();

            $table->foreign('online_poll_id')->references('id')->on('online_polls')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('poll_votes');
    }
}
