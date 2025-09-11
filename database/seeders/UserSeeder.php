<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách tên Việt Nam phổ biến
        $firstNames = [
            'Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi',
            'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý', 'Đinh', 'Đào', 'Tôn', 'Thái', 'Lương',
            'Mai', 'Cao', 'Đoàn', 'Tô', 'Hà', 'Lưu', 'Chu', 'Phùng', 'Vương', 'Quách'
        ];

        $middleNames = [
            'Văn', 'Thị', 'Đức', 'Minh', 'Quang', 'Hữu', 'Thanh', 'Xuân', 'Thu', 'Hạ',
            'Đông', 'Nam', 'Bắc', 'Tây', 'Trung', 'Anh', 'Tuấn', 'Hùng', 'Dũng', 'Mạnh',
            'Khánh', 'Phúc', 'Lộc', 'Thọ', 'Bình', 'Hòa', 'Tâm', 'Tài', 'Đức', 'Nhân'
        ];

        $lastNames = [
            'An', 'Bình', 'Cường', 'Dũng', 'Em', 'Giang', 'Hải', 'Ivan', 'Khang', 'Linh',
            'Minh', 'Nam', 'Oanh', 'Phong', 'Quân', 'Rosa', 'Sơn', 'Tâm', 'Uyên', 'Việt',
            'Xuân', 'Yến', 'Zoe', 'Anh', 'Bảo', 'Chi', 'Duy', 'Eva', 'Gia', 'Huy',
            'Iris', 'Khoa', 'Lan', 'Mai', 'Nga', 'Oanh', 'Phúc', 'Quỳnh', 'Rita', 'Sang',
            'Thảo', 'Uyên', 'Vân', 'Wendy', 'Xuan', 'Yen', 'Zara', 'An', 'Binh', 'Cuong'
        ];

        // Tạo 50 user
        for ($i = 0; $i < 50; $i++) {
            // Chọn tên ngẫu nhiên
            $firstName = $firstNames[array_rand($firstNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            // Tạo tên đầy đủ
            $fullName = $firstName . ' ' . $middleName . ' ' . $lastName;
            
            // Tạo email dựa trên tên
            $email = strtolower($lastName . $middleName . rand(1, 999) . '@gmail.com');
            
            // Tạo số điện thoại ngẫu nhiên
            $phone = '0' . rand(100, 999) . rand(1000000, 9999999);
            
            // Tạo thời gian ngẫu nhiên từ tháng 5/2025 đến hiện tại
            $startDate = Carbon::create(2025, 5, 1);
            $endDate = Carbon::now();
            $randomDate = Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));
            
            // Tạo role ngẫu nhiên (chủ yếu là USER)
            $roles = ['USER', 'USER', 'USER', 'USER', 'USER', 'ADMIN']; // 5/6 chance là USER
            $role = $roles[array_rand($roles)];
            
            User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make('password123'), // Mật khẩu mặc định
                'phone' => $phone,
                'role' => $role,
                'email_verified_at' => rand(0, 1) ? $randomDate : null, // 50% chance verified
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);
        }
        
        $this->command->info('Đã tạo thành công 50 user với tên ngẫu nhiên!');
    }
}