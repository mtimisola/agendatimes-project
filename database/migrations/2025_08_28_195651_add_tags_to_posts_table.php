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
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'post_title')) {
                $table->string('post_title')->after('id');
            }

            if (!Schema::hasColumn('posts', 'post_detail')) {
                $table->text('post_detail')->nullable()->after('post_title');
            }

            if (!Schema::hasColumn('posts', 'tags')) {
                $table->string('tags')->nullable()->after('post_detail');
            }

            if (!Schema::hasColumn('posts', 'sub_category_id')) {
                $table->unsignedBigInteger('sub_category_id')->nullable()->after('tags');
                $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'post_title')) {
                $table->dropColumn('post_title');
            }

            if (Schema::hasColumn('posts', 'post_detail')) {
                $table->dropColumn('post_detail');
            }

            if (Schema::hasColumn('posts', 'tags')) {
                $table->dropColumn('tags');
            }

            if (Schema::hasColumn('posts', 'sub_category_id')) {
                $table->dropForeign(['sub_category_id']);
                $table->dropColumn('sub_category_id');
            }
        });
    }
};
