<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;

class MaintenanceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bảo trì định kỳ hàng tháng',
                'description' => 'Kiểm tra và bảo trì định kỳ định kỳ mỗi tháng một lần.',
            ],
            [
                'name' => 'Bảo trì định kỳ hàng quý',
                'description' => 'Kiểm tra và bảo trì định kỳ định kỳ 3 tháng một lần.',
            ],
            [
                'name' => 'Bảo trì định kỳ hàng năm',
                'description' => 'Bảo dưỡng tổng thể và kiểm tra định kỳ mỗi năm một lần.',
            ],
            [
                'name' => 'Bảo trì hiệu chuẩn',
                'description' => 'Hiệu chuẩn các thông số đo lường và cảm biến của thiết bị.',
            ],
            [
                'name' => 'Bảo trì dự phòng',
                'description' => 'Thay thế linh kiện hao mòn trước khi xảy ra sự cố.',
            ],
            [
                'name' => 'Bảo trì khắc phục',
                'description' => 'Sửa chữa và khắc phục các sự cố đột xuất xảy ra trong quá trình vận hành.',
            ],
            [
                'name' => 'Kiểm tra an toàn',
                'description' => 'Đánh giá các điều kiện an toàn lao động và vận hành của máy móc.',
            ],
            [
                'name' => 'Vệ sinh & Bôi trơn',
                'description' => 'Làm sạch bụi bẩn và tra dầu mỡ định kỳ cho các chi tiết chuyển động.',
            ],
            [
                'name' => 'Nâng cấp phần cứng',
                'description' => 'Thay thế, cải tiến hoặc lắp đặt thêm các module phần cứng mới.',
            ],
            [
                'name' => 'Kiểm tra hiệu suất',
                'description' => 'Đo lường năng suất, tốc độ và độ chính xác của thiết bị.',
            ],
        ];

        foreach ($categories as $category) {
            MaintenanceCategory::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
