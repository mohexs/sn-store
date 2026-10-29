<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $today = now('Asia/Makassar')->toDateString();
        $startDate = $this->query('start_date') ?: $this->query('end_date') ?: $today;

        $this->merge([
            'start_date' => $startDate,
            'end_date' => $this->query('end_date') ?: $this->query('start_date') ?: $today,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }
}
