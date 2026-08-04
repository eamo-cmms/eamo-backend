<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Models\EquipmentError;
use Modules\Equipment\ErrorMonitoring\Services\StoreEquipmentErrorLogService;

try {
    $equip = Equipment::first();
    $error = EquipmentError::first();

    if (!$equip || !$error) {
        echo "Missing equip or error: equip=" . ($equip->id ?? 'none') . ", error=" . ($error->id ?? 'none') . "\n";
        exit;
    }

    echo "Testing insert for equip: {$equip->name} ({$equip->id}), error: {$error->name} ({$error->id})\n";

    $service = new StoreEquipmentErrorLogService();
    $result = $service->execute([
        'equipment_id' => $equip->id,
        'equipment_error_id' => $error->id,
        'occurred_at' => date('Y-m-d H:i:s'),
    ]);

    echo "SUCCESS! Created log ID: " . $result->id . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
