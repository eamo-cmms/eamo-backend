<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

final class DecodeQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qr_image' => ['required', 'image', 'max:5120'], // Max 5MB
        ];
    }
}
