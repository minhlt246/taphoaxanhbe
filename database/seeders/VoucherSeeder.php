<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Voucher;
use App\Models\User;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Bắt đầu tạo voucher cho các user...\n";
        
        // Lấy tất cả user (trừ admin)
        $users = User::where('role', '!=', 'ADMIN')->get();
        
        if ($users->isEmpty()) {
            echo "Không có user nào trong database!\n";
            return;
        }
        
        $voucherCount = 0;
        $voucherTypes = ['PERCENTAGE', 'NORMAL'];
        $voucherNames = [
            'GIAM10', 'GIAM20', 'GIAM30', 'GIAM50',
            'SALE10', 'SALE20', 'SALE30', 'SALE50',
            'WELCOME', 'LOYALTY', 'BIRTHDAY', 'SPECIAL'
        ];
        
        foreach ($users as $user) {
            // Tạo 1-3 voucher cho mỗi user
            $numberOfVouchers = rand(1, 3);
            
            for ($i = 0; $i < $numberOfVouchers; $i++) {
                $type = $voucherTypes[array_rand($voucherTypes)];
                $name = $voucherNames[array_rand($voucherNames)];
                $code = $name . $user->id . rand(100, 999);
                
                // Giá trị voucher
                if ($type === 'PERCENTAGE') {
                    $value = rand(10, 50); // 10-50%
                    $maxDiscount = rand(50000, 200000); // Tối đa 50k-200k
                } else {
                    $value = rand(20000, 100000); // 20k-100k VNĐ
                    $maxDiscount = $value;
                }
                
                // Số lượng sử dụng
                $quantity = 100; // Mỗi voucher có 100 lượt sử dụng
                
                // Thời gian
                $startDate = Carbon::now()->subDays(rand(0, 30));
                $endDate = $startDate->copy()->addDays(rand(30, 90));
                
                // Giá trị đơn hàng tối thiểu
                $minOrderValue = rand(100000, 500000);
                
                Voucher::create([
                    'code' => $code,
                    'type' => $type,
                    'value' => $value,
                    'max_discount' => $maxDiscount,
                    'min_order_value' => $minOrderValue,
                    'quantity' => $quantity,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_used' => false,
                    'order_id' => null,
                    'createdAt' => $startDate,
                    'updatedAt' => $startDate,
                ]);
                
                $voucherCount++;
            }
        }
        
        echo "Hoàn thành! Đã tạo $voucherCount voucher cho " . $users->count() . " user.\n";
    }
}
