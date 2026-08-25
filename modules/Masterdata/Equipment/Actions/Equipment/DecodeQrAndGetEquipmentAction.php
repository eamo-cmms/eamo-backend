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
        $file = $request->file('qr_image');

        // Gọi Service giải mã ảnh và tìm thiết bị
        $equipment = $service->decodeAndFind($file->getPathname());

        return response()->json([
            'message' => __('equipment.qr_decoded_success'),
            'data' => $equipment->load([
                'equipmentCategory',
                'equipmentErrors',
                'equipmentParameters.unit',
                'equipmentState',
                'equipmentImages'
            ])
        ]);
    }
}
