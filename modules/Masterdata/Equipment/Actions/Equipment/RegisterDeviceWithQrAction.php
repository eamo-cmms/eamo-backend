<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class RegisterDeviceWithQrAction
{
    use AsAction;

    /**
     * Handle registering an equipment: Generate UUID -> Generate QR Code -> Store QR in Storage -> Save to DB.
     *
     * @param array $equipmentData
     * @return Equipment
     */
    public function handle(array $equipmentData): Equipment
    {
        // 1. Generate UUID for device_id if not provided
        if (empty($equipmentData['device_id'])) {
            $equipmentData['device_id'] = (string) Str::uuid();
        }

        $uuid = $equipmentData['device_id'];

        try {
            // Sinh QR Code cục bộ dạng SVG bằng simplesoftwareio/simple-qrcode (không cần Imagick)
            $qrImage = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->generate($uuid);

            $qrFileName = "qrcodes/qr_{$uuid}.svg";
            Storage::disk('public')->put($qrFileName, $qrImage);

            // Gán đường dẫn URL công khai cho qr_code_path.
            $equipmentData['qr_code_path'] = '/storage/' . $qrFileName;
        } catch (\Exception $e) {
            logger()->error("Failed to generate QR code locally for equipment with device_id {$uuid}: " . $e->getMessage());
        }


        return Equipment::create($equipmentData);
    }
}
