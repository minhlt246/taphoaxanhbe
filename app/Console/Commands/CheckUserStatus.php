<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckUserStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và cập nhật trạng thái tài khoản người dùng dựa trên thời gian không hoạt động';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu kiểm tra trạng thái tài khoản người dùng...');
        
        $now = Carbon::now();
        $suspendedThreshold = $now->copy()->subDays(60); // 60 ngày
        $inactiveThreshold = $now->copy()->subDays(90);  // 90 ngày
        
        $suspendedCount = 0;
        $inactiveCount = 0;
        $notifiedCount = 0;
        
        // Lấy tất cả user có trạng thái active (bỏ qua admin)
        $activeUsers = User::where('status', 'active')
                          ->where('role', '!=', 'ADMIN')
                          ->get();
        
        foreach ($activeUsers as $user) {
            $lastActivity = $user->lastActivity ? Carbon::parse($user->lastActivity) : $user->createdAt;
            
            if ($lastActivity->lt($inactiveThreshold)) {
                // Tài khoản không hoạt động trên 90 ngày -> inactive
                $user->update([
                    'status' => 'inactive',
                    'statusUpdatedAt' => $now
                ]);
                
                $this->sendInactiveNotification($user);
                $inactiveCount++;
                $notifiedCount++;
                
                $this->info("Tài khoản {$user->name} ({$user->email}) đã được chuyển sang trạng thái INACTIVE");
                
            } elseif ($lastActivity->lt($suspendedThreshold)) {
                // Tài khoản không hoạt động trên 60 ngày -> suspended
                $user->update([
                    'status' => 'suspended',
                    'statusUpdatedAt' => $now
                ]);
                
                $this->sendSuspendedNotification($user);
                $suspendedCount++;
                $notifiedCount++;
                
                $this->info("Tài khoản {$user->name} ({$user->email}) đã được chuyển sang trạng thái SUSPENDED");
            }
        }
        
        // Gửi thông báo cho admin
        if ($suspendedCount > 0 || $inactiveCount > 0) {
            $this->sendAdminNotification($suspendedCount, $inactiveCount);
        }
        
        $this->info("Hoàn thành kiểm tra trạng thái:");
        $this->info("- Tài khoản bị tạm ngưng: {$suspendedCount}");
        $this->info("- Tài khoản bị ngưng hoạt động: {$inactiveCount}");
        $this->info("- Tổng số thông báo đã gửi: {$notifiedCount}");
        
        Log::info("User status check completed", [
            'suspended_count' => $suspendedCount,
            'inactive_count' => $inactiveCount,
            'notified_count' => $notifiedCount
        ]);
    }
    
    /**
     * Gửi thông báo tạm ngưng cho user
     */
    private function sendSuspendedNotification($user)
    {
        try {
            // Tạo email template đơn giản
            $subject = 'Tài khoản của bạn đã bị tạm ngưng - Tạp Hóa Xanh';
            $message = "
                <h2>Thông báo tạm ngưng tài khoản</h2>
                <p>Xin chào {$user->name},</p>
                <p>Tài khoản của bạn đã bị tạm ngưng do không hoạt động trong 60 ngày qua.</p>
                <p>Để kích hoạt lại tài khoản, vui lòng đăng nhập vào hệ thống.</p>
                <p>Trân trọng,<br>Đội ngũ Tạp Hóa Xanh</p>
            ";
            
            // Gửi email (cần cấu hình mail trong .env)
            Mail::raw(strip_tags($message), function ($mail) use ($user, $subject, $message) {
                $mail->to($user->email)
                     ->subject($subject)
                     ->html($message);
            });
            
            Log::info("Suspended notification sent to {$user->email}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send suspended notification to {$user->email}: " . $e->getMessage());
        }
    }
    
    /**
     * Gửi thông báo ngưng hoạt động cho user
     */
    private function sendInactiveNotification($user)
    {
        try {
            $subject = 'Tài khoản của bạn đã bị ngưng hoạt động - Tạp Hóa Xanh';
            $message = "
                <h2>Thông báo ngưng hoạt động tài khoản</h2>
                <p>Xin chào {$user->name},</p>
                <p>Tài khoản của bạn đã bị ngưng hoạt động do không hoạt động trong 90 ngày qua.</p>
                <p>Để kích hoạt lại tài khoản, vui lòng liên hệ với bộ phận hỗ trợ.</p>
                <p>Trân trọng,<br>Đội ngũ Tạp Hóa Xanh</p>
            ";
            
            Mail::raw(strip_tags($message), function ($mail) use ($user, $subject, $message) {
                $mail->to($user->email)
                     ->subject($subject)
                     ->html($message);
            });
            
            Log::info("Inactive notification sent to {$user->email}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send inactive notification to {$user->email}: " . $e->getMessage());
        }
    }
    
    /**
     * Gửi thông báo cho admin
     */
    private function sendAdminNotification($suspendedCount, $inactiveCount)
    {
        try {
            $adminEmails = User::where('role', 'ADMIN')->pluck('email')->toArray();
            
            if (empty($adminEmails)) {
                $adminEmails = ['admin@taphoxanh.com']; // Email admin mặc định
            }
            
            $subject = 'Báo cáo trạng thái tài khoản người dùng - Tạp Hóa Xanh';
            $message = "
                <h2>Báo cáo trạng thái tài khoản</h2>
                <p>Hệ thống đã tự động cập nhật trạng thái tài khoản người dùng:</p>
                <ul>
                    <li><strong>Tài khoản bị tạm ngưng:</strong> {$suspendedCount}</li>
                    <li><strong>Tài khoản bị ngưng hoạt động:</strong> {$inactiveCount}</li>
                </ul>
                <p>Vui lòng kiểm tra hệ thống để xem chi tiết.</p>
                <p>Trân trọng,<br>Hệ thống Tạp Hóa Xanh</p>
            ";
            
            foreach ($adminEmails as $adminEmail) {
                Mail::raw(strip_tags($message), function ($mail) use ($adminEmail, $subject, $message) {
                    $mail->to($adminEmail)
                         ->subject($subject)
                         ->html($message);
                });
            }
            
            Log::info("Admin notification sent to: " . implode(', ', $adminEmails));
            
        } catch (\Exception $e) {
            Log::error("Failed to send admin notification: " . $e->getMessage());
        }
    }
}