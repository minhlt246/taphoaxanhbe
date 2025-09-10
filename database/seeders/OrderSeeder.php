<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Bắt đầu tạo đơn hàng cho các user...\n";
        
        // Lấy tất cả user (trừ admin)
        $users = User::where('role', '!=', 'ADMIN')->get();
        $products = Product::all();
        
        if ($products->isEmpty()) {
            echo "Không có sản phẩm nào trong database!\n";
            return;
        }
        
        $orderCount = 0;
        $totalOrders = $users->count();
        
        foreach ($users as $user) {
            // Tạo 1-3 đơn hàng cho mỗi user
            $numberOfOrders = rand(1, 3);
            
            for ($i = 0; $i < $numberOfOrders; $i++) {
                // Thời gian đơn hàng: 1-2 ngày sau ngày tạo user
                $userCreatedAt = Carbon::parse($user->createdAt);
                $orderDate = $userCreatedAt->addDays(rand(1, 2))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                
                // Tạo đơn hàng
                $orderCode = 'ORD' . $user->id . $orderCount . rand(1000, 9999); // Tạo order_code unique
                $order = Order::create([
                    'userId' => $user->id,
                    'order_code' => $orderCode,
                    'total_price' => 0, // Sẽ cập nhật sau
                    'status' => 'completed',
                    'payment_status' => 'completed',
                    'note' => 'Đơn hàng tự động tạo',
                    'createdAt' => $orderDate,
                    'updatedAt' => $orderDate,
                ]);
                
                // Tạo order items (1-5 sản phẩm mỗi đơn hàng)
                $numberOfItems = rand(1, 5);
                $selectedProducts = $products->random($numberOfItems);
                $orderTotal = 0;
                
                foreach ($selectedProducts as $product) {
                    // Đảm bảo số lượng không âm
                    $availableQuantity = max(0, $product->quantity);
                    if ($availableQuantity <= 0) {
                        continue; // Bỏ qua sản phẩm hết hàng
                    }
                    
                    $quantity = rand(1, min(10, $availableQuantity)); // Không vượt quá số lượng có sẵn
                    $price = $product->price;
                    $itemTotal = $quantity * $price;
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $price,
                        'createdAt' => $orderDate,
                        'updatedAt' => $orderDate,
                    ]);
                    
                    // Cập nhật số lượng sản phẩm (đảm bảo không âm)
                    $newQuantity = max(0, $product->quantity - $quantity);
                    $product->update([
                        'quantity' => $newQuantity,
                        'status' => $newQuantity > 0 ? 'active' : 'inactive'
                    ]);
                    $product->increment('purchase', $quantity);
                    
                    $orderTotal += $itemTotal;
                }
                
                // Cập nhật tổng giá trị đơn hàng (đảm bảo từ 1-5 triệu)
                $minTotal = 1000000; // 1 triệu
                $maxTotal = 5000000; // 5 triệu
                
                if ($orderTotal > 0) {
                    if ($orderTotal < $minTotal) {
                        // Nếu tổng quá thấp, tăng giá một số sản phẩm
                        $multiplier = $minTotal / $orderTotal;
                        $order->orderItems()->update([
                            'unit_price' => \DB::raw("unit_price * $multiplier")
                        ]);
                        $orderTotal = $minTotal;
                    } elseif ($orderTotal > $maxTotal) {
                        // Nếu tổng quá cao, giảm giá
                        $multiplier = $maxTotal / $orderTotal;
                        $order->orderItems()->update([
                            'unit_price' => \DB::raw("unit_price * $multiplier")
                        ]);
                        $orderTotal = $maxTotal;
                    }
                } else {
                    // Nếu không có sản phẩm, tạo một sản phẩm giả
                    $randomProduct = $products->random();
                    $quantity = rand(1, 5);
                    $price = $minTotal / $quantity; // Đảm bảo tổng = 1 triệu
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $randomProduct->id,
                        'quantity' => $quantity,
                        'unit_price' => $price,
                        'createdAt' => $orderDate,
                        'updatedAt' => $orderDate,
                    ]);
                    
                    $orderTotal = $minTotal;
                }
                
                // Sử dụng voucher ngẫu nhiên (100% chance - tất cả user đều sử dụng)
                if (true) {
                    $availableVouchers = \App\Models\Voucher::where('is_used', false)
                        ->where('start_date', '<=', $orderDate)
                        ->where('end_date', '>=', $orderDate)
                        ->where('min_order_value', '<=', $orderTotal)
                        ->get();
                    
                    if ($availableVouchers->isNotEmpty()) {
                        $voucher = $availableVouchers->random();
                        $discountAmount = $voucher->calculateDiscount($orderTotal);
                        $orderTotal = max(0, $orderTotal - $discountAmount);
                        
                        // Cập nhật voucher (không giảm quantity, chỉ cập nhật is_used khi hết lượt)
                        $usedCount = $voucher->getUsedCount() + 1;
                        if ($usedCount >= $voucher->quantity) {
                            $voucher->update(['is_used' => true]);
                        }
                        
                        // Tạo voucher usage record
                        \App\Models\VoucherUsage::create([
                            'voucher_id' => $voucher->id,
                            'user_id' => $user->id,
                            'order_id' => $order->id,
                            'discount_amount' => $discountAmount,
                            'used_at' => $orderDate,
                            'created_at' => $orderDate,
                            'updated_at' => $orderDate,
                        ]);
                        
                        echo "Sử dụng voucher {$voucher->code} cho đơn hàng {$order->order_code}, giảm " . number_format($discountAmount) . " VNĐ\n";
                    }
                }
                
                $order->update(['total_price' => $orderTotal]);
                
                $orderCount++;
            }
        }
        
        echo "Hoàn thành! Đã tạo $orderCount đơn hàng cho $totalOrders user.\n";
    }
}
