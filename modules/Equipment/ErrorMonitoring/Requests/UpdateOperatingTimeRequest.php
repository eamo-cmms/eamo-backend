<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;

final class UpdateOperatingTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', OperatingTime::class) ?? false;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'equipment_id' => 'sometimes|required|string|max:36',
            'equipment_name' => 'nullable|string',
            'planned_stop_time' => 'sometimes|required|numeric',
            'unplanned_stop_time' => 'nullable|numeric',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
        ];
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['equipment_id', 'start_time', 'end_time'])) {
                    return;
                }

                $id = $this->route('id');
                if (! $id) {
                    return;
                }

                $time = OperatingTime::find($id);
                if (! $time) {
                    return;
                }

                $equipmentId = $this->input('equipment_id') ?? $time->equipment_id;
                $startTime = $this->input('start_time') ?? $time->start_time;
                $endTime = $this->input('end_time') ?? $time->end_time;

                $overlapExists = OperatingTime::query()
                    ->where('equipment_id', $equipmentId)
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($overlapExists) {
                    $validator->errors()->add(
                        'start_time',
                        __('The operating time overlaps with an existing operating time for this equipment.')
                    );
                }
            },
        ];
    }
}
