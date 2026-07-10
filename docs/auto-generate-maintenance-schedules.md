# Tính năng: Tự Động Sinh Lịch Bảo Trì (Auto-Generate Maintenance Schedules)

## 1. Tổng Quan Ý Tưởng

Khi người dùng tạo hoặc cập nhật một **Maintenance Plan** (`eamo_maintenance_plans`), hệ thống sẽ **tự động sinh ra các bản ghi lịch bảo trì** (`eamo_maintenance_schedules`) dựa trên:

- `maintenance_category_id` của Plan → tìm các **Maintenance Items** (`eamo_maintenance_items`) thuộc cùng category đó.
- `date` (start date) nhập từ UI → ngày bắt đầu tính lịch.
- `cycle_type` + `cycle_interval` + `occurrences` → chu kỳ lặp lại và số lần lặp để tính các ngày tiếp theo.

**Mục tiêu**: Thay vì người dùng phải nhập thủ công từng schedule, hệ thống tự động sinh ra toàn bộ lịch theo chu kỳ cho tất cả maintenance items thuộc category của plan.

---

## 2. Cơ Sở Dữ Liệu Liên Quan

### Bảng `eamo_maintenance_categories`
| Cột | Kiểu | Mô tả |
|-----|------|--------|
| `id` | string(36) PK | UUID |
| `name` | string | Tên danh mục bảo trì |
| `description` | text | Mô tả |

### Bảng `eamo_maintenance_items`
| Cột | Kiểu | Mô tả |
|-----|------|--------|
| `id` | string(36) PK | UUID |
| `maintenance_category_id` | string(36) FK | Thuộc danh mục nào |
| `name` | string | Tên hạng mục bảo trì |
| `description` | string | Mô tả |

### Bảng `eamo_maintenance_plans` *(có thêm cột mới)*
| Cột | Kiểu | Mô tả |
|-----|------|--------|
| `id` | string(36) PK | UUID |
| `plan_code` | string | Mã kế hoạch |
| `equipment_id` | string(36) | Thiết bị cần bảo trì |
| `maintenance_category_id` | string(36) FK | **Danh mục bảo trì** → dùng để tìm items |
| `date` | date | **Ngày bắt đầu** (start date từ UI) |
| `cycle_type` | string | **Loại chu kỳ**: `daily`, `weekly`, `monthly`, `yearly` |
| `cycle_interval` | integer | **Số đơn vị chu kỳ**: VD: `2` → cứ 2 tuần/tháng/... |
| `occurrences` | integer | ⭐ **[MỚI] Số lần lặp lại** (tối đa 100) |
| `start_time` | time | Giờ bắt đầu trong ngày |
| `end_time` | time | Giờ kết thúc trong ngày |
| `maintenance_type` | string | Loại bảo trì |
| `notes` | text | Ghi chú |

### Bảng `eamo_maintenance_schedules`
| Cột | Kiểu | Mô tả |
|-----|------|--------|
| `id` | string(36) PK | UUID |
| `maintenance_plan_id` | string(36) FK | Thuộc kế hoạch nào |
| `equipment_id` | string(36) | Thiết bị (copy từ plan) |
| `maintenance_item_id` | string(36) FK nullable | Hạng mục bảo trì cụ thể |
| `date` | date | **Ngày thực hiện** (được tính tự động theo chu kỳ) |

### Bảng `eamo_maintenance_logs`
| Cột | Kiểu | Mô tả |
|-----|------|--------|
| `id` | string(36) PK | UUID |
| `maintenance_schedule_id` | string(36) FK | Thuộc schedule nào |
| `log_date` | date | Ngày thực hiện thực tế |
| `result` | string | Kết quả bảo trì |
| `note` | string | Ghi chú |

---

## 3. Logic Tính Toán Lịch

### 3.1. Xác Định Danh Sách Items

```sql
SELECT * FROM eamo_maintenance_items
WHERE maintenance_category_id = plan.maintenance_category_id
```

### 3.2. Tính Các Ngày Theo Chu Kỳ + Số Lần Lặp

**Quy tắc**: Từ `date` (start date), tính `occurrences` ngày theo `cycle_type` và `cycle_interval`.

| `cycle_type` | `cycle_interval` | `occurrences` | Kết quả |
|-------------|-----------------|--------------|---------|
| `daily` | 1 | 5 | 5 ngày liên tiếp |
| `daily` | 3 | 4 | 4 mốc, mỗi mốc cách nhau 3 ngày |
| `weekly` | 1 | 4 | 4 tuần (mỗi tuần 1 lần) |
| `weekly` | 2 | 3 | 3 mốc, mỗi mốc cách nhau 2 tuần |
| `monthly` | 1 | 12 | 12 tháng (cả năm) |
| `monthly` | 3 | 4 | 4 quý trong năm |
| `yearly` | 1 | 3 | 3 năm liên tiếp |

**Giới hạn**: `occurrences` tối đa là **100**.

**Công thức tính ngày thứ `i`** (i = 0, 1, 2, ...):
```
date_i = start_date + (i × cycle_interval × đơn_vị_của_cycle_type)
```

### 3.3. Sinh Schedules

Với mỗi **ngày** × mỗi **maintenance item** → 1 bản ghi `eamo_maintenance_schedules`.

**Số schedules tạo ra** = `occurrences × số_items_trong_category` ≤ **100**

> ⚠️ Giới hạn tổng số schedules là **100** (không phải số occurrences là 100). Nếu `occurrences × items_count > 100`, hệ thống sẽ throw exception / trả về lỗi validation.

**Ví dụ minh họa:**

- Plan: `maintenance_category_id = "cat-001"`, `date = 2025-01-01`, `cycle_type = "monthly"`, `cycle_interval = 1`, `occurrences = 3`
- Items thuộc "cat-001": `item-A` (Kiểm tra dầu), `item-B` (Vệ sinh bộ lọc)
- Kết quả sinh ra (3 × 2 = **6 schedules**):

| maintenance_plan_id | maintenance_item_id | date |
|---------------------|---------------------|------|
| plan-001 | item-A | 2025-01-01 |
| plan-001 | item-B | 2025-01-01 |
| plan-001 | item-A | 2025-02-01 |
| plan-001 | item-B | 2025-02-01 |
| plan-001 | item-A | 2025-03-01 |
| plan-001 | item-B | 2025-03-01 |

---

## 4. Khi Nào Trigger Sinh Lịch?

| Sự kiện | Hành động |
|---------|-----------|
| **Tạo Plan mới** (`POST /maintenance-plans`) | Sinh toàn bộ schedules theo chu kỳ (nếu có `cycle_type`) |
| **Cập nhật Plan** (`PUT /maintenance-plans/{id}`) | **Chỉ xóa schedules chưa có log** → sinh lại mới; schedules đã được log giữ nguyên |
| **Xóa Plan** (`DELETE /maintenance-plans/{id}`) | Xóa cascade toàn bộ schedules liên quan |

### Điều Kiện Trigger Regenerate Khi Update

Chỉ regenerate khi một trong các field sau thay đổi:
- `cycle_type`
- `cycle_interval`
- `occurrences`
- `date` (start date)
- `maintenance_category_id`

### Bảo Vệ Schedules Đã Thực Hiện

Khi **regenerate** (update plan), hệ thống:
1. Query các schedules của plan **đã có ít nhất 1 maintenance log**:
   ```sql
   SELECT DISTINCT maintenance_schedule_id FROM eamo_maintenance_logs
   WHERE maintenance_schedule_id IN (schedules của plan)
   ```
2. **Giữ nguyên** những schedules đó.
3. **Xóa** các schedules chưa có log.
4. Sinh mới các schedules theo chu kỳ mới.

> ⚠️ Giới hạn 100 schedules được tính trên **tổng số schedules mới** (không bao gồm schedules cũ đã có log).

---

## 5. Kiến Trúc Triển Khai

### 5.1. Database Migration

Thêm cột `occurrences` vào `eamo_maintenance_plans`:

```php
// Migration mới
Schema::table('eamo_maintenance_plans', function (Blueprint $table) {
    $table->unsignedSmallInteger('occurrences')->nullable()->after('cycle_interval');
});
```

### 5.2. Service Class: `MaintenanceScheduleGeneratorService`

```
modules/Equipment/Maintenance/Services/MaintenanceScheduleGeneratorService.php
```

```php
final class MaintenanceScheduleGeneratorService
{
    public const MAX_SCHEDULES = 100;

    /**
     * Sinh danh sách ngày theo chu kỳ.
     *
     * @return CarbonImmutable[]
     */
    public function generateDates(
        CarbonImmutable $startDate,
        string $cycleType,       // 'daily' | 'weekly' | 'monthly' | 'yearly'
        int $cycleInterval,
        int $occurrences,
    ): array { ... }

    /**
     * Sinh và lưu schedules cho plan.
     * Ném exception nếu occurrences × items_count > MAX_SCHEDULES.
     */
    public function generateForPlan(MaintenancePlan $plan): void { ... }

    /**
     * Regenerate: xóa schedules chưa có log, giữ schedules đã có log, sinh mới.
     */
    public function regenerateForPlan(MaintenancePlan $plan): void { ... }
}
```

### 5.3. Cập Nhật Actions

**`StoreMaintenancePlanAction`**:
```
1. Validate + tạo MaintenancePlan (có occurrences)
2. Nếu plan.cycle_type != null → gọi service::generateForPlan($plan)
   Nếu không → bỏ qua (không có schedules tự động)
3. Return plan + schedules
```

**`UpdateMaintenancePlanAction`**:
```
1. Validate + cập nhật MaintenancePlan
2. Nếu có thay đổi ở [cycle_type, cycle_interval, occurrences, date, maintenance_category_id]
   → gọi service::regenerateForPlan($plan)
3. Return plan + schedules
```

### 5.4. Cập Nhật Request Validation

**`StoreMaintenancePlanRequest`** — thêm:
```php
'occurrences' => ['nullable', 'integer', 'min:1', 'max:100'],
```

**`UpdateMaintenancePlanRequest`** — thêm:
```php
'occurrences' => ['nullable', 'integer', 'min:1', 'max:100'],
```

---

## 6. Luồng Hoạt Động (Flow)

```
UI nhập:
  - equipment_id
  - maintenance_category_id  ← xác định bảo trì cái gì
  - date (start date)        ← bắt đầu từ ngày nào
  - cycle_type               ← loại chu kỳ (daily/weekly/monthly/yearly)
  - cycle_interval           ← mỗi N đơn vị
  - occurrences              ← lặp bao nhiêu lần (tối đa 100)
  - maintenance_type
  - start_time, end_time
  - notes

Backend xử lý:
  1. Tạo/Cập nhật MaintenancePlan record
  2. Query items: SELECT * FROM eamo_maintenance_items WHERE maintenance_category_id = ?
  3. Kiểm tra: occurrences × items.count ≤ 100 (nếu không → lỗi 422)
  4. Tính danh sách dates:
       date_0 = start_date
       date_1 = start_date + (1 × cycle_interval × cycle_type_unit)
       ...
       date_{occurrences-1}
  5. Với mỗi (date_i, item_j):
       INSERT INTO eamo_maintenance_schedules
         (maintenance_plan_id, equipment_id, maintenance_item_id, date)
       VALUES (plan.id, plan.equipment_id, item_j.id, date_i)
  6. Return plan + schedules
```

---

## 7. Checklist Triển Khai

- [ ] **Migration**: Thêm `occurrences` (`unsignedSmallInteger nullable`) vào `eamo_maintenance_plans`
- [ ] **Model**: Thêm `occurrences` vào `$fillable` của `MaintenancePlan`
- [ ] **Service**: Tạo `MaintenanceScheduleGeneratorService` với:
  - [ ] `generateDates()` — tính ngày theo chu kỳ
  - [ ] `generateForPlan()` — sinh schedules từ đầu
  - [ ] `regenerateForPlan()` — giữ schedules đã có log, xóa cái chưa có log, sinh lại
- [ ] **StoreMaintenancePlanAction**: Bỏ phần nhập schedules thủ công, gọi service nếu có `cycle_type`
- [ ] **UpdateMaintenancePlanAction**: Gọi `regenerateForPlan()` khi cycle fields thay đổi
- [ ] **Request Validation**: Thêm `occurrences` vào Store + Update requests, loại bỏ `schedules` manual input
- [ ] **Tests (Pest)**:
  - [ ] Unit test `generateDates()` cho cả 4 cycle_type
  - [ ] Feature test Store plan → sinh đúng số schedules
  - [ ] Feature test Update plan → giữ schedules đã logged, sinh lại cái chưa logged
  - [ ] Feature test vượt 100 schedules → trả về lỗi 422
