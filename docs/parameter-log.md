# Tài liệu Luồng Ghi Nhật Ký Thông Số Thiết Bị (Parameter Log)

Tài liệu này mô tả chi tiết cơ sở dữ liệu, các API và logic nghiệp vụ theo dõi, ghi nhận nhật ký thông số vận hành (Parameter Logs) của thiết bị trong hệ thống EAMO.

---

## 1. Cơ Sở Dữ Liệu (`eamo_equipment_parameter_logs`)

Bảng `eamo_equipment_parameter_logs` đóng vai trò lưu trữ các giá trị đo lường thực tế của từng thông số kỹ thuật (Nhiệt độ, Áp suất, Rung động, Điện áp...) theo thời gian:

- `id` (UUID PK): Mã định danh duy nhất.
- `equipment_id` (UUID FK): Thiết bị được đo thông số.
- `equipment_parameter_id` (UUID FK): Thông số kỹ thuật được đo.
- `unit_id` (UUID FK): Đơn vị đo lường tại thời điểm ghi.
- `user_id` (UUID FK): Kỹ sư / Nhân viên ghi nhận giá trị.
- `value` (float): Giá trị đo lường thực tế.
- `recorded_at` (datetime): Thời điểm đo thực tế.
- `note` (text nullable): Ghi chú bổ sung.

---

## 2. Các Logic Nghiệp Vụ Chính

### 2.1. Xem Ma Trận Nhật Ký Thông Số 7 Ngày (`GetWeeklyEquipmentParameterLogsAction`)
- **API**: `GET /api/v1/equipment/equipment-parameter/logs/weekly/{equipmentId}`
- **Quyền**: `engineer`
- **Mục tiêu**: Lấy toàn bộ lịch sử thông số của một thiết bị trong vòng 7 ngày gần nhất để vẽ biểu đồ diễn biến thông số hoặc hiển thị bảng ma trận theo dõi.
- **Logic xử lý**:
  1. Lấy mốc thời gian 7 ngày trước: `$oneWeekAgo = CarbonImmutable::now()->subDays(7)->startOfDay();`.
  2. Truy vấn các bản ghi `EquipmentParameterLog` của `equipmentId` có `recorded_at >= $oneWeekAgo` (hoặc `created_at >= $oneWeekAgo` nếu `recorded_at` null).
  3. Load các mối quan hệ: `equipment`, `parameter`, `unit`, `user`.
  4. Trả về kết quả sắp xếp mới nhất lên đầu (`latest('recorded_at')`).

### 2.2. Lưu Nhật Ký Thông Số Hàng Loạt (`SaveEquipmentParameterLogAction`)
- **API**: `POST /api/v1/equipment/equipment-parameter/logs/save`
- **Quyền**: `manager`
- **Mục tiêu**: Cho phép kỹ sư nhập một danh sách nhiều thông số của thiết bị cùng một lúc trong một lượt đi ca kiểm tra.

### 2.3. Import Nhật Ký Thông Số Từ Excel (`ImportEquipmentParameterLogAction`)
- **API**: `POST /api/v1/equipment/equipment-parameter/logs/import`
- **Quyền**: `manager`
- **Mục tiêu**: Hỗ trợ nhập dữ liệu lịch sử thông số từ file Excel mẫu.

---

## 3. Danh Sách Endpoints API Nhật Ký Thông Số

| Method | Endpoint | Middleware | Mô tả |
|---|---|---|---|
| GET | `/api/v1/equipment/equipment-parameter/logs` | `engineer` | Danh sách nhật ký thông số (phân trang) |
| GET | `/api/v1/equipment/equipment-parameter/logs/weekly/{equipmentId}` | `engineer` | Lịch sử thông số 7 ngày gần nhất của thiết bị |
| GET | `/api/v1/equipment/equipment-parameter/logs/overview/{id}` | `engineer` | Thống kê tổng quan nhật ký thông số |
| GET | `/api/v1/equipment/equipment-parameter/logs/{id}` | `engineer` | Chi tiết một bản ghi thông số |
| POST | `/api/v1/equipment/equipment-parameter/logs` | `manager` | Thêm mới 1 bản ghi thông số |
| POST | `/api/v1/equipment/equipment-parameter/logs/save` | `manager` | Lưu hàng loạt danh sách thông số |
| POST | `/api/v1/equipment/equipment-parameter/logs/import` | `manager` | Import dữ liệu thông số từ Excel |
| PUT | `/api/v1/equipment/equipment-parameter/logs/{id}` | `manager` | Cập nhật giá trị thông số |
| DELETE | `/api/v1/equipment/equipment-parameter/logs/{id}` | `manager` | Xóa bản ghi thông số |
