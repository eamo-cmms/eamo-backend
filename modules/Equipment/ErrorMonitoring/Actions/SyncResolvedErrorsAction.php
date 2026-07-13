<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Services\SyncResolvedErrorsService;

/**
 * Sync resolved error logs back to equipment:
 * For each error log that has handled_at set (resolved), detach that error
 * from the equipment's associated errors pivot table.
 *
 * If a specific log ID is given via route param, only sync that one.
 * Otherwise, sync ALL resolved logs.
 */
final class SyncResolvedErrorsAction
{
    use AsAction;

    public function asController(
        Request $request,
        SyncResolvedErrorsService $service,
        ?string $id = null
    ): JsonResponse {
        try {
            $synced = $service->execute($id);

            if ($id) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Error log detached from equipment successfully.',
                    'synced' => $synced,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => "Successfully synced {$synced} error log records.",
                'synced' => $synced,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
