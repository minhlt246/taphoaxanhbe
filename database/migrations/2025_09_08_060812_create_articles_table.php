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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->text('summary');
            $table->unsignedBigInteger('author_id');
            $table->string('author_name');
            $table->string('author_avatar')->nullable();
            $table->string('category');
            $table->json('tags')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_rejected')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->timestamps();
            
            // Foreign key will be added after all tables are created
            // $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
