<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $today = now('Asia/Makassar');
        $this->merge([
            'type' => $this->input('type', 'daily'),
            'start_date' => $this->filled('start_date') ? $this->input('start_date') : $today->copy()->startOfMonth()->toDateString(),
            'end_date' => $this->filled('end_date') ? $this->input('end_date') : $today->toDateString(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        $type = $this->input('type');

        return route('admin.reports.index', ['type' => in_array($type, ['daily', 'products', 'orders'], true) ? $type : 'daily']);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['daily', 'products', 'orders'])],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['revenue', 'quantity', 'name'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'type.in' => 'Jenis laporan tidak tersedia.',
            'start_date.date_format' => 'Tanggal mulai harus berformat tahun-bulan-tanggal.',
            'end_date.date_format' => 'Tanggal akhir harus berformat tahun-bulan-tanggal.',
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
            'q.string' => 'Kata pencarian harus berupa teks.',
            'q.max' => 'Kata pencarian maksimal 100 karakter.',
            'sort.in' => 'Pilihan urutan tidak tersedia.',
            'page.integer' => 'Nomor halaman harus berupa angka.',
            'page.min' => 'Nomor halaman minimal 1.',
        ];
    }
}
