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
     * @param  string  $imagePath  Physical path of the image file
     *
     * @throws \Exception
     */
    public function decodeAndFind(string $imagePath): Equipment
    {
        // 1. Giải mã hình ảnh dùng QrReader
        $qrcode = new QrReader($imagePath);
        $uuid = $qrcode->text();

        if (empty($uuid)) {
            throw new \Exception(__('equipment.no_qr_found'), 422);
        }

        // 2. Tìm kiếm thiết bị theo ID hoặc device_id
        $equipment = Equipment::where(function ($query) use ($uuid) {
            $query->where('id', $uuid)
                ->orWhere('device_id', $uuid);
        })->first();

        if (! $equipment) {
            throw new \Exception(__('equipment.equipment_not_found'), 404);
        }

        return $equipment;
    }
}
