<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\Masterdata\Equipment\Models\Equipment;

final class SyncEquipmentImagesService
{
    /**
     * Upload and attach new images to the equipment.
     *
     * @param  Equipment  $equipment
     * @param  array<int, UploadedFile>  $files
     */
    public function uploadImages(Equipment $equipment, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('equipment_images', 'public');
            $equipment->equipmentImages()->create([
                'image_id' => (string) Str::uuid(),
                'path' => '/storage/'.$path,
            ]);
        }
    }

    /**
     * Sync images: delete unselected existing images and upload new ones.
     *
     * @param  Equipment  $equipment
     * @param  array<int, string>  $existingImageIds
     * @param  array<int, UploadedFile>  $newFiles
     */
    public function sync(Equipment $equipment, array $existingImageIds = [], array $newFiles = []): void
    {
        $equipment->equipmentImages()
            ->whereNotIn('id', $existingImageIds)
            ->delete();

        if (! empty($newFiles)) {
            $this->uploadImages($equipment, $newFiles);
        }
    }
}
