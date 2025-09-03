<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCommentsTableForThreadingAndLikes extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('post_id');
            $table->integer('likes')->default(0)->after('comment');
            $table->integer('dislikes')->default(0)->after('likes');
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'likes', 'dislikes']);
        });
    }
}
