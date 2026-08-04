<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tự động xóa sạch dữ liệu cũ trong bảng khi chạy migration để tránh lỗi khóa ngoại
        Schema::disableForeignKeyConstraints();
        DB::table('eamo_equipment_error_logs')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Truncate is a destructive action and cannot be undone via down()
    }
};
