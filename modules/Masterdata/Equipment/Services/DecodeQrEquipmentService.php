<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Modules\Masterdata\Equipment\Models\Equipment;
use Zxing\QrReader;

final class DecodeQrEquipmentService
{
    /**
     * Decode a QR code image and find the corresponding equipment.
     *
     * @param string $imagePath Physical path of the image file
     * @return Equipment
     * @throws \Exception
     */
    public function decodeAndFind(string $imagePath): Equipment
    {
        // 1. Giải mã hình ảnh dùng QrReader
        $qrcode = new QrReader($imagePath);
        $uuid = $qrcode->text();

        if (empty($uuid)) {
            throw new \Exception('No QR code found or unable to decode QR from this image.', 422);
        }

        // 2. Tìm kiếm thiết bị theo ID hoặc device_id (kể cả đã bị xóa tạm)
        $equipment = Equipment::withTrashed()
            ->where(function ($query) use ($uuid) {
                $query->where('id', $uuid)
                      ->orWhere('device_id', $uuid);
            })
            ->first();

        if (! $equipment) {
            throw new \Exception('Thiết bị không tồn tại trong hệ thống.', 404);
        }

        if ($equipment->trashed()) {
            throw new \Exception('Thiết bị đã bị xóa (soft deleted).', 410);
        }

        return $equipment;
    }
}
