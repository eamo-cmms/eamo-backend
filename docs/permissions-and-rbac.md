# Hướng Dẫn Kỹ Thuật Phân Quyền Động & RBAC (Developer Guide)

Tài liệu này dành cho các lập trình viên phát triển Backend và Frontend trong hệ thống EAMO. Quy định rõ kiến trúc phân quyền, ranh giới Role và hướng dẫn thêm quyền mới khi phát triển tính năng.

---

## 1. Triết Lý Thiết Kế (Core Philosophy)

> [!IMPORTANT]
> **Quy định quan trọng:** Hệ thống **KHÔNG cung cấp tính năng CRUD tạo/xóa mã quyền (Permission Definition) trên giao diện người dùng**.
> Toàn bộ danh mục mã quyền (`permission_code`) phải được **quản lý tập trung trong Code (Seeder)** để đảm bảo tính toàn vẹn với các logic kiểm tra quyền trong `Policy` và `FormRequest`.

```
┌───────────────────────────────┐        ┌───────────────────────────────┐
│     Codebase / Developer      │        │        Admin / Operator       │
├───────────────────────────────┤        ├───────────────────────────────┤
│ • Định nghĩa mã Permission    │        │ • Phân bổ / Bật / Tắt quyền   │
│ • Viết Policy & Gate checks   │ ─────► │   cho từng Manager / Engineer │
│ • Quản lý PermissionSeeder    │        │ • Thao tác 100% bằng tay trên │
│ • Zero-Config Auto-Discovery  │        │   giao diện Web Console       │
└───────────────────────────────┘        └───────────────────────────────┘
```

* **Mã quyền (`id`) là bất biến trong logic chạy:** Một quyền chỉ có ý nghĩa khi có Policy/Gate tương ứng trong mã nguồn kiểm tra nó. Nếu cho phép người dùng tự tạo mã quyền tùy ý trên UI, các mã đó sẽ không được backend xử lý.
* **Gán quyền (Assignment) là động 100%:** Admin toàn quyền bật/tắt các quyền cụ thể cho từng tài khoản `Manager` và `Engineer` mà không cần sửa code hay can thiệp cơ sở dữ liệu.

---

## 2. Ma Trận Ranh Giới Role (Role Boundaries)

Hệ thống tuân thủ nghiêm ngặt 4 ranh giới quyền hạn được cài đặt ở tầng `HasPermissions` Trait và `Policy`:

| Role | Quyền Hạn Tối Đa | Cơ Chế Kiểm Soát |
| :--- | :--- | :--- |
| **Admin** | **100% Toàn quyền** | `Gate::before` bypass tự động. Không cần gán quyền thủ công. |
| **Guest** | **Chỉ Xem (View-Only)** | Được cấp quyền `view`, `viewAny`. Toàn bộ hành vi CRUD (`create`, `update`, `delete`, `judge`, `save`) bị **khóa cứng** để bảo vệ dữ liệu. |
| **Engineer** | **Chuyên trách Kỹ thuật** | **Chặn hoàn toàn** khỏi phân hệ Tổ chức (`company.*`, `department.*`, `user.*`) ở cả Policy và Request layer. Được phân quyền linh hoạt trong Masterdata, Checklist, Bảo trì và Nhật ký. |
| **Manager** | **Quản trị Toàn diện** | Được phép phân bổ cả quyền Quản trị Tổ chức và quyền Kỹ thuật theo danh sách quyền động do Admin cấp. |

---

## 3. Quy Trình 4 Bước Thêm Quyền Mới Khi Phát Triển Tính Năng

Khi phát triển một tính năng nghiệp vụ mới (ví dụ: *Xuất báo cáo chi phí bảo trì*), lập trình viên thực hiện theo 4 bước sau:

### Bước 1: Khai báo Permission trong `PermissionSeeder.php`
Mở file [`backend/database/seeders/PermissionSeeder.php`](file:///c:/Users/khanh/Projects/eamo/backend/database/seeders/PermissionSeeder.php) và thêm bản ghi mới vào nhóm tương ứng:

```php
[
    'id' => 'maintenance.export_report', // Định dạng: {domain}.{action}
    'name' => 'Export Maintenance Report',
    'group' => 'maintenance', // organization | equipment_masterdata | checklist | maintenance | monitoring_logs
    'description' => 'Allow exporting maintenance cost and downtime reports to Excel/PDF',
],
```

### Bước 2: Viết kiểm tra quyền trong `Policy` hoặc `FormRequest`
Sử dụng helper `$user->hasPermission(...)` trong Policy tương ứng:

```php
namespace Modules\Equipment\Maintenance\Policies;

use App\Models\User;

class MaintenancePlanPolicy
{
    public function export(User $user): bool
    {
        return $user->hasPermission('maintenance.export_report');
    }
}
```

Trong FormRequest (nếu cần bảo vệ tại tầng Request Validation):
```php
public function authorize(): bool
{
    return $this->user()?->hasPermission('maintenance.export_report') ?? false;
}
```

### Bước 3: Cập nhật Database Seeder
Chạy lệnh seeder để cập nhật danh mục quyền vào database:
```bash
php artisan db:seed --class=PermissionSeeder
```

### Bước 4: Thêm nhãn dịch vào Frontend (i18n)
Mở [`frontend/src/locales/langs/zh-CN/page.json`](file:///c:/Users/khanh/Projects/eamo/frontend/src/locales/langs/zh-CN/page.json) và [`en-US/page.json`](file:///c:/Users/khanh/Projects/eamo/frontend/src/locales/langs/en-US/page.json) để thêm mô tả tiếng Việt và tiếng Anh.

---

## 4. Cơ Chế Auto-Discovery Của Laravel Policy

Backend áp dụng cơ chế tự động tìm Policy chuẩn, không cần đăng ký thủ công mảng `$policies`:

```php
// app/Providers/AppServiceProvider.php
Gate::guessPolicyNamesUsing(function (string $modelClass): string {
    return str_replace('\\Models\\', '\\Policies\\', $modelClass) . 'Policy';
});
```

**Quy tắc đặt tên bắt buộc:**
* Model: `App\Models\Company` $\rightarrow$ Policy: `App\Policies\CompanyPolicy`
* Model: `Modules\Masterdata\Equipment\Models\Equipment` $\rightarrow$ Policy: `Modules\Masterdata\Equipment\Policies\EquipmentPolicy`

---

## 5. Danh Sách REST APIs Phân Quyền Cho Admin

* **`GET /api/permissions`**: Lấy danh sách toàn bộ quyền theo nhóm (hỗ trợ `?role=engineer` để tự động lọc bỏ nhóm tổ chức).
* **`GET /api/users/{user}/permissions`**: Lấy danh sách mã quyền đang gán cho user mục tiêu.
* **`PUT /api/users/{user}/permissions`**: Cập nhật mảng mã quyền cho user:
  ```json
  {
    "permissions": [
      "equipment.view",
      "equipment.create",
      "checklist.judge"
    ]
  }
  ```
