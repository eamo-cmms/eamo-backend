<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Requests\Equipment\DecodeQrRequest;
use Modules\Masterdata\Equipment\Services\DecodeQrEquipmentService;

final class DecodeQrAndGetEquipmentAction
{
    use AsAction;

    public function asController(
        DecodeQrRequest $request,
        DecodeQrEquipmentService $service
    ): JsonResponse {
        try {
            $file = $request->file('qr_image');

            // Gọi Service giải mã ảnh và tìm thiết bị
            $equipment = $service->decodeAndFind($file->getPathname());

            return response()->json([
                'message' => 'QR code decoded successfully!',
                'data' => $equipment->load([
                    'equipmentCategory',
                    'equipmentErrors',
                    'equipmentParameters.unit',
                    'equipmentState',
                    'equipmentImages'
                ])
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode();
            // Đảm bảo mã lỗi HTTP hợp lệ (trong khoảng 400 - 599)
            $httpCode = ($code >= 400 && $code < 600) ? $code : 500;

            return response()->json([
                'message' => $e->getMessage()
            ], $httpCode);
        }
    }
}
