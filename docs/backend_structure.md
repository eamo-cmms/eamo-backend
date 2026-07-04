# Cấu trúc Thư mục Backend Application (`backend/app`)

Tài liệu này mô tả kiến trúc thiết kế và vai trò của các thư mục nằm bên trong thư mục lõi ứng dụng `backend/app` của dự án EAMO. Dự án được phát triển dựa trên Laravel với mô hình thiết kế phân lớp (Layered Architecture) giúp mã nguồn sạch sẽ, dễ bảo trì và mở rộng.

---

## Danh sách các thư mục chính trong `backend/app`

### 1. `Bridge`
- **Vai trò**: Chứa các lớp cầu nối hoặc lớp đè (override) để tích hợp, tinh chỉnh hoạt động của các thư viện bên thứ ba (Third-party Packages) tích hợp trong hệ thống.
- **Ví dụ**: 
  - `AccessToken.php` và `AccessTokenRepository.php` dùng để can thiệp vào luồng mã hóa JWT của thư viện Laravel Passport, hỗ trợ chèn thêm thông tin về quyền (`roles`) vào token.

### 2. `Builders`
- **Vai trò**: Chứa các lớp Eloquent Query Builder tùy chỉnh. Thay vì viết các câu lệnh truy vấn phức tạp (như `where`, `join`, `orderBy`) trực tiếp trong Model hoặc Controller, chúng được gom nhóm vào các lớp Builder chuyên biệt để tái sử dụng và kiểm thử dễ dàng hơn.
- **Ví dụ**:
  - `UserQueryBuilder.php` chứa các phương thức hỗ trợ truy vấn riêng cho thực thể người dùng.

### 3. `Http`
- **Vai trò**: Lớp xử lý giao thức truyền tải dữ liệu HTTP (giao diện giao tiếp với Client).
- **Thư mục con bên trong**:
  - `Controllers`: Chứa các bộ điều khiển nhận request, gọi Service xử lý và trả về response. Dự án áp dụng thiết kế Single Action Controller (Invokable Controller) - mỗi tệp tin chỉ xử lý duy nhất một hành động (Action) cụ thể để giữ mã nguồn cực kỳ gọn gàng.
  - `Requests`: Chứa các lớp validation để kiểm tra tính hợp lệ của dữ liệu đầu vào (Form Request Validation) trước khi chuyển tiếp vào luồng xử lý.
  - `Resources`: Chứa các lớp API Resource (Transformer) định dạng lại cấu trúc dữ liệu JSON trả về cho phía Client (Frontend).
  - `Middleware`: Bộ lọc request trước khi đi vào Controllers (như kiểm tra đăng nhập, phân quyền).

### 4. `Models`
- **Vai trò**: Chứa các lớp Eloquent Models đại diện cho cấu trúc bảng trong cơ sở dữ liệu PostgreSQL. Các lớp này chủ yếu dùng để định nghĩa các mối quan hệ (Relationships) giữa các thực thể và cấu hình các thuộc tính cast kiểu dữ liệu.

### 5. `Providers`
- **Vai trò**: Chứa các Service Providers. Đây là nơi cấu hình, đăng ký (register) và khởi động (boot) toàn bộ các dịch vụ hệ thống của framework (như cấu hình Laravel Passport, đăng ký Repository, v.v.).

### 6. `Services`
- **Vai trò**: Lớp xử lý logic nghiệp vụ chính (Business Logic Layer) của hệ thống. Lớp này nhận dữ liệu từ Controller, thực hiện các nghiệp vụ tính toán, xử lý database và trả kết quả về. Tách biệt hoàn toàn logic nghiệp vụ ra khỏi Controller giúp hệ thống dễ dàng bảo trì và tối ưu kiểm thử tự động (Unit/Feature Tests).
