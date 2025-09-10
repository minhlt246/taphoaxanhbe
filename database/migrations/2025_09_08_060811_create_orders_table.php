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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_price', 10, 2);
            $table->text('note')->nullable();
            $table->string('order_code')->unique();
            $table->string('status')->default('pending');
            $table->string('payment')->default('pending');
            $table->unsignedBigInteger('user_id');
            $table->softDeletes();
            $table->timestamps();
            
            // Foreign key will be added after all tables are created
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
