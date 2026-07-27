<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;

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
            // fetch api tạo QR
            $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($uuid);
            $response = Http::timeout(10)->get($qrApiUrl);

            if ($response->successful()) {
                // Lưu trữ vào storage
                $qrFileName = "qrcodes/qr_{$uuid}.png";
                Storage::disk('public')->put($qrFileName, $response->body());

                // Gán đường dẫn URL công khai cho qr_code_path.
                $equipmentData['qr_code_path'] = '/storage/' . $qrFileName;
            }
        } catch (\Exception $e) {
    
            logger()->error("Failed to generate QR code for equipment with device_id {$uuid}: " . $e->getMessage());
        }


        return Equipment::create($equipmentData);
    }
}
