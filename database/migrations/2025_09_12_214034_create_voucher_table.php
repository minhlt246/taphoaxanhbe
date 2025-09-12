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
        Schema::create('voucher', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('max_discount');
            $table->integer('min_order_value')->default(0);
            $table->integer('quantity')->default(0);
            $table->boolean('is_used')->default(false);
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->integer('user_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->string('type')->default('discount');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deletedAt')->nullable();
            
            $table->index('code');
            $table->index('is_used');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher');
    }
};