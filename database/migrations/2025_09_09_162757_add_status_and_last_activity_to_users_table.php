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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active')->after('role')->comment('Trạng thái tài khoản');
            $table->timestamp('lastActivity')->nullable()->after('status')->comment('Lần hoạt động cuối cùng');
            $table->timestamp('statusUpdatedAt')->nullable()->after('lastActivity')->comment('Thời gian cập nhật trạng thái cuối');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'lastActivity', 'statusUpdatedAt']);
        });
    }
};