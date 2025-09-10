<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Bắt đầu tạo đánh giá sản phẩm cho các user...\n";
        
        // Lấy tất cả user (trừ admin)
        $users = User::where('role', '!=', 'ADMIN')->get();
        
        if ($users->isEmpty()) {
            echo "Không có user nào trong database!\n";
            return;
        }
        
        $products = Product::all();
        if ($products->isEmpty()) {
            echo "Không có sản phẩm nào trong database!\n";
            return;
        }
        
        $reviewCount = 0;
        
        // Đánh giá tốt
        $goodComments = [
            'Sản phẩm rất tốt, chất lượng cao!',
            'Tôi rất hài lòng với sản phẩm này.',
            'Đóng gói cẩn thận, giao hàng nhanh.',
            'Sản phẩm đúng như mô tả, rất chất lượng.',
            'Tôi sẽ mua lại sản phẩm này.',
            'Chất lượng vượt mong đợi!',
            'Sản phẩm tươi ngon, giá cả hợp lý.',
            'Dịch vụ tốt, sản phẩm chất lượng.',
            'Rất hài lòng với lần mua này.',
            'Sản phẩm tốt, đáng tiền bạc.',
            'Giao hàng nhanh, sản phẩm tươi.',
            'Chất lượng tốt, tôi rất thích.',
            'Sản phẩm đúng như quảng cáo.',
            'Tôi khuyên mọi người nên mua.',
            'Sản phẩm tuyệt vời!'
        ];
        
        // Đánh giá xấu
        $badComments = [
            'Sản phẩm không như mong đợi.',
            'Chất lượng kém, không xứng đáng với giá tiền.',
            'Giao hàng chậm, sản phẩm bị hỏng.',
            'Sản phẩm cũ, không tươi.',
            'Tôi không hài lòng với sản phẩm này.',
            'Chất lượng kém, tôi thất vọng.',
            'Sản phẩm không đúng mô tả.',
            'Giao hàng chậm, dịch vụ kém.',
            'Sản phẩm bị lỗi, không sử dụng được.',
            'Tôi không khuyên mua sản phẩm này.',
            'Chất lượng tồi, giá đắt.',
            'Sản phẩm không tươi, có mùi lạ.',
            'Đóng gói cẩu thả, sản phẩm bị vỡ.',
            'Dịch vụ kém, sản phẩm không đạt.',
            'Tôi rất thất vọng với sản phẩm này.'
        ];
        
        foreach ($users as $user) {
            // Mỗi user đánh giá ít nhất 2 sản phẩm, nhiều nhất 8 sản phẩm
            $numberOfReviews = rand(2, 8);
            
            // Chọn sản phẩm ngẫu nhiên để đánh giá
            $selectedProducts = $products->random($numberOfReviews);
            
            foreach ($selectedProducts as $product) {
                // Rating từ 1-5 sao
                $rating = rand(1, 5);
                
                // Chọn comment dựa trên rating
                if ($rating >= 4) {
                    $comment = $goodComments[array_rand($goodComments)];
                } else {
                    $comment = $badComments[array_rand($badComments)];
                }
                
                // Thời gian đánh giá (từ tháng 5 đến hiện tại)
                $reviewDate = Carbon::create(2025, 5, 1)
                    ->addDays(rand(0, 120))
                    ->addHours(rand(0, 23))
                    ->addMinutes(rand(0, 59));
                
                // Status ngẫu nhiên
                $statuses = ['pending', 'approved', 'rejected'];
                $status = $statuses[array_rand($statuses)];
                
                Review::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'status' => $status,
                    'createdAt' => $reviewDate,
                    'updatedAt' => $reviewDate,
                ]);
                
                $reviewCount++;
            }
        }
        
        echo "Hoàn thành! Đã tạo $reviewCount đánh giá cho " . $users->count() . " user.\n";
    }
}
