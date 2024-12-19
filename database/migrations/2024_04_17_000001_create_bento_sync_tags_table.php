<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bento_sync_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name');
            $table->timestamps();

            // Ensure tag names are unique
            $table->unique('tag_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bento_sync_tags');
    }
};
