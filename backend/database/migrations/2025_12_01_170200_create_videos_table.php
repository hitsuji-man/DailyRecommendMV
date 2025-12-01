<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_id',20)->unique();
            $table->foreignId('artist_id')->constrained('artists')->cascadeOnDelete();
            // 複合UNIQUE制約(同じアーティストの同じ動画が重複登録されない)
            $table->unique(['youtube_id', 'artist_id']);
            $table->string('title');
            $table->text('description');
            $table->string('channel_id', 50);
            $table->string('channel_title');
            $table->string('thumbnail_url');
            $table->dateTime('published_at')->nullable();
            $table->bigInteger('view_count')->nullable();
            $table->bigInteger('like_count')->nullable();
            $table->enum('source_type',['trend', 'channel', 'mixed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
