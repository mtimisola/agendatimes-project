<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            // status can be 'pending' (guest waiting for approval),
            // 'published' (visible), or 'draft' (hidden/unpublished)
            if (!Schema::hasColumn('posts', 'status')) {
                $table->enum('status', ['pending','published','draft'])->default('published')->after('headline_section');
            }
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
