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
            throw new \Exception('Không tìm thấy hoặc không thể giải mã QR từ hình ảnh này.', 422);
        }

        // 2. Tìm kiếm thiết bị theo ID hoặc device_id
        $equipment = Equipment::where('id', $uuid)
            ->orWhere('device_id', $uuid)
            ->first();

        if (! $equipment) {
            throw new \Exception('Mã QR hợp lệ nhưng không tìm thấy thiết bị tương ứng trong hệ thống.', 404);
        }

        return $equipment;
    }
}
