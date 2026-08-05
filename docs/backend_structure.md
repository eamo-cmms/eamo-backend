# Kiến trúc Tổng quan Backend Application (`backend/`)

Tài liệu này mô tả chi tiết kiến trúc thiết kế, các quy tắc tổ chức mã nguồn và vai trò của các thư mục bên trong hệ thống backend EAMO (Equipment Asset & Maintenance Operation). 

Dự án được xây dựng trên nền tảng **Laravel 13** (PHP 8.4) áp dụng kết hợp giữa **Layered Architecture (Kiến trúc phân lớp)** và **Modular Architecture (Kiến trúc mô-đun)** giúp hệ thống đạt tính mô-đun hóa cao, dễ dàng bảo trì và mở rộng.

---

## 1. Cấu trúc Tổng thể Thư mục

```
backend/
├── app/                      # Các thành phần lõi của ứng dụng
│   ├── Bridge/               # Lớp cầu nối tinh chỉnh thư viện 3rd party (Laravel Passport JWT)
│   ├── Builders/             # Eloquent Query Builders tùy chỉnh
│   ├── Concerns/             # Traits tái sử dụng trong hệ thống
│   ├── Enums/                # Các Enumerations định nghĩa hằng số nghiệp vụ
│   ├── Extensions/           # Mở rộng các thành phần của Framework
│   ├── Http/                 # Controllers, Requests, Resources, Middleware
│   ├── Models/               # Eloquent Models cơ sở (User, Company, Department, Notification)
│   ├── Notifications/        # Các thông báo hệ thống (Database, Mail...)
│   ├── Providers/            # Service Providers đăng ký dịch vụ
│   ├── Rules/                # Custom Validation Rules
│   └── Services/             # Logic nghiệp vụ chung (Cascade Soft Delete...)
├── config/                   # Cấu hình hệ thống (Auth, CORS, Passport...)
├── database/                 # Migrations, Seeders, Factories
├── docs/                     # Tài liệu kỹ thuật chi tiết của dự án
├── modules/                  # Các Mô-đun nghiệp vụ chuyên biệt
│   ├── Equipment/            # Mô-đun Quản lý Thiết bị & Vận hành
│   │   ├── Checklist/        # Quản lý & Tự động sinh Lịch Kiểm tra
│   │   ├── ErrorMonitoring/  # Giám sát Lỗi & Thời gian Vận hành
│   │   ├── Maintenance/      # Quản lý & Tự động sinh Lịch Bảo trì
│   │   ├── ParameterLog/     # Nhật ký Thông số Vận hành Thiết bị
│   │   └── Services/         # Dịch vụ chung mô-đun Equipment
│   └── Masterdata/           # Mô-đun Quản lý Dữ liệu Danh mục
│       └── Equipment/        # Danh mục Thiết bị, Thông số, Lỗi, Trạng thái, Đơn vị
├── routes/                   # Đĩnh tuyến ứng dụng (api.php, auth.php, console.php, web.php)
└── tests/                    # Bộ kiểm thử tự động (Pest / PHPUnit)
```

---

## 2. Chi tiết các Thư mục & Thành phần Lõi (`app/`)

### 2.1. `Bridge` (Thư viện Cầu nối)
- **Mục đích**: Tùy biến và can thiệp vào hành vi mặc định của thư viện thứ ba.
- **Thành phần chính**:
  - [`AccessToken.php`](file:///c:/Users/khanh/Projects/eamo/backend/app/Bridge/AccessToken.php) & [`AccessTokenRepository.php`](file:///c:/Users/khanh/Projects/eamo/backend/app/Bridge/AccessTokenRepository.php): Override luồng phát hành JWT của Laravel Passport để nhúng thêm claim `roles` (danh sách quyền của người dùng) trực tiếp vào JWT Payload.

### 2.2. `Builders` (Query Builders Tùy chỉnh)
- **Mục đích**: Tách biệt các câu lệnh truy vấn phức tạp ra khỏi Model và Controller.
- **Thành phần chính**:
  - `UserQueryBuilder.php`: Chứa các phương thức truy vấn riêng cho thực thể `User`.

### 2.3. `Http` (Giao tiếp HTTP & Routing)
- **Controllers**: Dự án áp dụng mô hình **Single Action Controller (Invokable Controller)**. Mỗi file controller chỉ chứa hàm `__invoke()` hoặc kế thừa `AsAction` (Lorisleiva\Actions) để xử lý **duy nhất 1 hành động**.
- **Requests**: Chứa các lớp Form Request Validation kiểm tra tính hợp lệ của dữ liệu đầu vào.
- **Resources**: Chứa API Resources (Transformers) để định dạng JSON response trả về cho Frontend.
- **Middleware**: Bộ lọc bảo mật như `own.user` (kiểm tra tài khoản chính chủ), `admin`, `manager`, `engineer`.

### 2.4. `Services` (Lớp Xử lý Nghiệp vụ)
- **Mục đích**: Chứa toàn bộ Business Logic của hệ thống. Controller chỉ nhận Request, gọi Service xử lý và trả về Response.
- **Thành phần chính**:
  - `EquipmentCascadeSoftDeleteService`: Dịch vụ dọn dẹp và xóa liên hoàn (soft delete) các bản ghi phụ thuộc khi xóa thiết bị, checklist hoặc bảo trì.

---

## 3. Cấu trúc Mô-đun Nghiệp vụ (`modules/`)

Hệ thống được chia làm 2 mô-đun chính:

### 3.1. `Modules\Masterdata`
Quản lý các dữ liệu danh mục nền tảng:
- **Equipment Masterdata**: Quản lý Thiết bị (`Equipment`), Danh mục Thiết bị (`EquipmentCategory`), Thông số Thiết bị (`EquipmentParameter`), Danh mục Lỗi (`EquipmentError`), Trạng thái (`EquipmentState`), Đơn vị đo (`Unit`).

### 3.2. `Modules\Equipment`
Quản lý các hoạt động vận hành & bảo trì thiết bị:
- **Checklist**: Quản lý cấu hình checklist mẫu (`ChecklistSession`), hạng mục chi tiết (`ChecklistDetail`), lịch kiểm tra (`ChecklistSchedule`), kết quả kiểm tra (`ChecklistLog`) và dịch vụ sinh lịch tự động `ChecklistScheduleGeneratorService`.
- **Maintenance**: Quản lý kế hoạch bảo trì (`MaintenancePlan`), danh mục bảo trì (`MaintenanceCategory`), hạng mục (`MaintenanceItem`), lịch bảo trì (`MaintenanceSchedule`), nhật ký (`MaintenanceLog`) và dịch vụ sinh lịch tự động `MaintenanceScheduleGeneratorService`.
- **ErrorMonitoring**: Giám sát nhật ký lỗi (`EquipmentErrorLog`) và thời gian vận hành thực tế (`OperatingTime`), tính toán số giờ còn lại trước khi cần bảo trì.
- **ParameterLog**: Quản lý và ghi nhận thông số vận hành theo thời gian thực (`EquipmentParameterLog`), xem ma trận thông số 7 ngày và hỗ trợ nhập từ file Excel.

---

## 4. Các Nguyên tắc Kiến trúc Quan trọng (Design Principles)

1. **Strict Types & Declarations**: 100% file PHP trong dự án đều khai báo `declare(strict_types=1);` và khai báo kiểu dữ liệu trả về/tham số rõ ràng.
2. **Single Action Action/Controller**: Sử dụng gói `lorisleiva/laravel-actions` hoặc Invokable Controller giúp code cực kỳ gọn gàng.
3. **Database UUID**: Bảng `users`, `oauth_*` và toàn bộ thực thể trong mô-đun đều dùng UUID làm Primary Key.
4. **Cascade Soft Delete**: Đảm bảo toàn vẹn dữ liệu khi xóa bằng dịch vụ xóa liên hoàn.
5. **Format API Response**: Toàn bộ API trả về response đồng nhất theo định dạng `{ "status": "success", "data": ... }` hoặc `{ "message": "...", "data": ... }`.
