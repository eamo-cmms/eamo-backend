<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;
use Modules\Masterdata\Equipment\Models\EquipmentState;
use Modules\Masterdata\Equipment\Models\Unit;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {

        // Define categories
        $categories = [
            'CNC Machines' => [
                ['name' => 'CNC Milling Machine Haas VF-2', 'code' => 'CNC-HAAS-VF2'],
                ['name' => 'CNC Lathe Mazak Quick Turn 250', 'code' => 'CNC-MAZAK-QT250'],
                ['name' => 'CNC Router Biesse Rover K', 'code' => 'CNC-BIESSE-RK'],
                ['name' => 'CNC Laser Cutter Trumpf TruLaser 3030', 'code' => 'CNC-TRUMPF-TL3030'],
                ['name' => 'CNC Plasma Cutter Hypertherm XPR300', 'code' => 'CNC-HYP-XPR300'],
                ['name' => '5-Axis CNC DMG Mori DMU 50', 'code' => 'CNC-MORI-DMU50'],
            ],
            'Injection Molding Machines' => [
                ['name' => 'Arburg Allrounder 370', 'code' => 'IMM-ARBURG-370'],
                ['name' => 'Engel Victory 120', 'code' => 'IMM-ENGEL-V120'],
                ['name' => 'Sumitomo Demag SE180EV', 'code' => 'IMM-SUMI-SE180'],
                ['name' => 'Husky HyPET5 HPP5', 'code' => 'IMM-HUSKY-H5'],
                ['name' => 'Fanuc Roboshot S-2000i', 'code' => 'IMM-FANUC-S2000'],
                ['name' => 'Nissei FNX220', 'code' => 'IMM-NISSEI-FNX220'],
            ],
            'Industrial Robots' => [
                ['name' => 'Fanuc M-20iB/25', 'code' => 'ROB-FANUC-M20'],
                ['name' => 'ABB IRB 2600', 'code' => 'ROB-ABB-IRB2600'],
                ['name' => 'KUKA KR 60-3', 'code' => 'ROB-KUKA-KR60'],
                ['name' => 'Yaskawa Motoman GP25', 'code' => 'ROB-YAS-GP25'],
                ['name' => 'Universal Robots UR10e', 'code' => 'ROB-UR-UR10E'],
                ['name' => 'Kawasaki RS020N', 'code' => 'ROB-KAW-RS20N'],
            ],
            'Packaging Machines' => [
                ['name' => 'Vertical Form Fill Seal Bosch Terra 25', 'code' => 'PKG-BOSCH-T25'],
                ['name' => 'Rotary Cartoner Bosch CUC', 'code' => 'PKG-BOSCH-CUC'],
                ['name' => 'Flow Wrapper Ishida FW-2000', 'code' => 'PKG-ISHI-FW2000'],
                ['name' => 'Shrink Wrapper Cryovac 200', 'code' => 'PKG-CRYO-200'],
                ['name' => 'Case Packer Multivac T300', 'code' => 'PKG-MULTI-T300'],
            ],
            'Metal Stamping Press' => [
                ['name' => 'Mechanical Press Aida NC1-110', 'code' => 'PRS-AIDA-NC1'],
                ['name' => 'Hydraulic Press Beckwood 100 Ton', 'code' => 'PRS-BECK-100T'],
                ['name' => 'Pneumatic Press Schmidt 55', 'code' => 'PRS-SCHM-55'],
                ['name' => 'Progressive Die Press Komatsu OBX110', 'code' => 'PRS-KOMA-OB110'],
                ['name' => 'Servo Press Amada SDE 2025', 'code' => 'PRS-AMADA-SDE'],
            ],
        ];

        foreach ($categories as $catName => $equipments) {
            $cat = EquipmentCategory::create([
                'id' => (string) Str::uuid(),
                'code' => Str::slug($catName),
                'name' => $catName,
            ]);

            foreach ($equipments as $eq) {
                $equipment = Equipment::create([
                    'id' => (string) Str::uuid(),
                    'equipment_category_id' => $cat->id,
                    'name' => $eq['name'],
                    'code' => $eq['code'],
                    'maintenance_interval_hours' => rand(200, 2000),
                    'is_active' => true,
                ]);

                // Seed initial equipment state
                $states = ['Running', 'Idle', 'Under Maintenance', 'Stopped', 'Fault'];
                EquipmentState::create([
                    'id' => (string) Str::uuid(),
                    'equipment_id' => $equipment->id,
                    'state' => $states[array_rand($states)],
                ]);

                // Seed some parameters
                $params = [
                    [
                        'name' => 'Operating Temperature',
                        'code' => $eq['code'].'-TEMP',
                        'standard' => 65.0,
                        'max' => 85.0,
                        'min' => 20.0,
                        'unit' => '°C',
                    ],
                    [
                        'name' => 'Power Consumption',
                        'code' => $eq['code'].'-PWR',
                        'standard' => 15.5,
                        'max' => 22.0,
                        'min' => 1.0,
                        'unit' => 'kW',
                    ],
                    [
                        'name' => 'Working Pressure',
                        'code' => $eq['code'].'-PRES',
                        'standard' => 6.2,
                        'max' => 8.0,
                        'min' => 4.5,
                        'unit' => 'bar',
                    ],
                ];

                foreach ($params as $p) {
                    $unit = Unit::firstOrCreate([
                        'code' => $p['unit'],
                    ], [
                        'name' => $p['unit'],
                    ]);

                    $eqParam = EquipmentParameter::create([
                        'id' => (string) Str::uuid(),
                        'equipment_id' => $equipment->id,
                        'name' => $p['name'],
                        'code' => $p['code'],
                        'unit_id' => $unit->id,
                        'equipment_category_id' => $cat->id,
                        'standard' => $p['standard'],
                        'standard_max' => $p['max'],
                        'standard_min' => $p['min'],
                    ]);
                }
            }
        }
    }
}
