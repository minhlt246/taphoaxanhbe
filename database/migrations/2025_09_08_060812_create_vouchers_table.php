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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('max_discount', 10, 2)->default(0);
            $table->decimal('min_order_value', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->boolean('is_used')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('type', ['PERCENTAGE', 'NORMAL'])->default('NORMAL');
            $table->decimal('value', 10, 2);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();
            
            // Foreign key will be added after all tables are created
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
