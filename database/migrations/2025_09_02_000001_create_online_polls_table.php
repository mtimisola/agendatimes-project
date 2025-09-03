<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlinePollsTable extends Migration
{
    public function up()
    {
        Schema::create('online_polls', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('expiration')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->integer('voting_limit')->default(1);
            $table->unsignedBigInteger('language_id')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('online_polls');
    }
}
