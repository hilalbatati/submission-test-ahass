<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Jam servis yang tersedia (08:00 - 17:00).
     *
     * @var list<string>
     */
    public const AVAILABLE_SLOTS = [
        '08:00',
        '09:00',
        '10:00',
        '11:00',
        '12:00',
        '13:00',
        '14:00',
        '15:00',
        '16:00',
        '17:00',
    ];

    /**
     * Menentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang diterapkan pada request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plate_number' => ['required', 'string', 'max:20'],
            'customer_name' => ['required', 'string', 'max:100'],
            'motorcycle_type' => ['required', 'string', 'max:100'],
            'service_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'service_time' => ['required', 'string', Rule::in(self::AVAILABLE_SLOTS)],
            'service_package_id' => ['required', 'integer', 'exists:service_packages,id'],
        ];
    }

    /**
     * Pesan validasi kustom dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plate_number.required' => 'Plat nomor kendaraan wajib diisi.',
            'plate_number.max' => 'Plat nomor maksimal 20 karakter.',
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'customer_name.max' => 'Nama pelanggan maksimal 100 karakter.',
            'motorcycle_type.required' => 'Tipe motor wajib diisi (contoh: Vario 160, Beat, PCX).',
            'motorcycle_type.max' => 'Tipe motor maksimal 100 karakter.',
            'service_date.required' => 'Tanggal servis wajib dipilih.',
            'service_date.date' => 'Format tanggal servis tidak valid.',
            'service_date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'service_date.after_or_equal' => 'Tanggal servis minimal hari ini.',
            'service_time.required' => 'Jam servis wajib dipilih.',
            'service_time.in' => 'Jam servis harus di antara 08:00 sampai 17:00.',
            'service_package_id.required' => 'Paket servis wajib dipilih.',
            'service_package_id.exists' => 'Paket servis yang dipilih tidak ditemukan.',
        ];
    }

    /**
     * Menangani validasi yang gagal untuk request AJAX/JSON.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Data pendaftaran belum lengkap atau tidak valid.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
