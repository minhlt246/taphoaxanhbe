<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tạo danh mục mặc định với id = 0 nếu chưa tồn tại
        $defaultCategory = DB::table('category')->where('id', 0)->first();
        
        if (!$defaultCategory) {
            DB::table('category')->insert([
                'id' => 0,
                'name' => 'Chưa có danh mục nào',
                'slug' => 'chua-co-danh-muc-nao',
                'parent_id' => null,
                'image_url' => null,
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa danh mục mặc định
        DB::table('category')->where('id', 0)->delete();
    }
};