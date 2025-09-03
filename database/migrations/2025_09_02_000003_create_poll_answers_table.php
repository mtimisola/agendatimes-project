<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePollAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('poll_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('poll_question_id');
            $table->string('text');
            $table->integer('votes')->default(0);
            $table->timestamps();

            $table->foreign('poll_question_id')->references('id')->on('poll_questions')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('poll_answers');
    }
}
