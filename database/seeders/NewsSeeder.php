<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\User;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('Không có user nào để tạo tin tức');
            return;
        }

        $newsData = [
            [
                'title' => 'Công nghệ AI đang thay đổi cách chúng ta mua sắm',
                'content' => 'Trí tuệ nhân tạo (AI) đang ngày càng được ứng dụng rộng rãi trong lĩnh vực thương mại điện tử. Từ việc gợi ý sản phẩm phù hợp đến chatbot hỗ trợ khách hàng, AI đang tạo ra những trải nghiệm mua sắm hoàn toàn mới.',
                'summary' => 'AI đang cách mạng hóa thương mại điện tử với những tính năng thông minh',
                'category' => 'Công nghệ',
                'tags' => 'AI, thương mại điện tử, công nghệ',
            ],
            [
                'title' => 'Xu hướng mua sắm online sau đại dịch',
                'content' => 'Đại dịch COVID-19 đã thay đổi hoàn toàn thói quen mua sắm của người tiêu dùng. Nhiều người đã chuyển sang mua sắm online và xu hướng này vẫn tiếp tục phát triển mạnh mẽ.',
                'summary' => 'Mua sắm online trở thành xu hướng chủ đạo sau đại dịch',
                'category' => 'Thương mại',
                'tags' => 'mua sắm online, đại dịch, xu hướng',
            ],
            [
                'title' => 'Bảo mật thông tin trong thương mại điện tử',
                'content' => 'Với sự phát triển của thương mại điện tử, vấn đề bảo mật thông tin người dùng ngày càng trở nên quan trọng. Các doanh nghiệp cần đầu tư mạnh vào hệ thống bảo mật để bảo vệ khách hàng.',
                'summary' => 'Bảo mật thông tin là ưu tiên hàng đầu trong thương mại điện tử',
                'category' => 'Bảo mật',
                'tags' => 'bảo mật, thông tin, thương mại điện tử',
            ],
            [
                'title' => 'Tương lai của thanh toán điện tử',
                'content' => 'Các phương thức thanh toán điện tử đang phát triển nhanh chóng với sự xuất hiện của ví điện tử, tiền điện tử và các công nghệ blockchain. Điều này mở ra nhiều cơ hội mới cho ngành thương mại.',
                'summary' => 'Thanh toán điện tử đang định hình tương lai của thương mại',
                'category' => 'Tài chính',
                'tags' => 'thanh toán điện tử, ví điện tử, blockchain',
            ],
            [
                'title' => 'Tối ưu hóa trải nghiệm người dùng trên mobile',
                'content' => 'Với hơn 70% người dùng truy cập website thông qua thiết bị di động, việc tối ưu hóa trải nghiệm mobile đã trở thành yếu tố quyết định thành công của một trang thương mại điện tử.',
                'summary' => 'Mobile-first là chiến lược quan trọng cho thương mại điện tử',
                'category' => 'UX/UI',
                'tags' => 'mobile, UX, UI, tối ưu hóa',
            ],
        ];

        foreach ($newsData as $index => $data) {
            $user = $users->random();
            
            News::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'summary' => $data['summary'],
                'author_id' => $user->id,
                'author_name' => $user->name,
                'author_avatar' => null,
                'category' => $data['category'],
                'tags' => $data['tags'],
                'featured_image' => null,
                'images' => null,
                'is_published' => rand(0, 1) == 1,
                'is_approved' => rand(0, 1) == 1,
                'is_rejected' => rand(0, 10) == 1, // 10% chance of rejection
                'rejection_reason' => null,
                'published_at' => rand(0, 1) == 1 ? now()->subDays(rand(1, 30)) : null,
                'view_count' => rand(10, 1000),
                'like_count' => rand(0, 100),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);
        }

        $this->command->info('Đã tạo ' . count($newsData) . ' tin tức mẫu');
    }
}