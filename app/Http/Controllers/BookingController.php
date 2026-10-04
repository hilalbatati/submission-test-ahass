<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ServicePackage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Kuota maksimal kendaraan per slot jam.
     */
    public const MAX_QUOTA_PER_SLOT = 3;

    /**
     * Menampilkan halaman utama SPA booking servis.
     */
    public function index(): View
    {
        return view('booking');
    }

    /**
     * Mengambil daftar paket servis AHASS.
     */
    public function packages(): JsonResponse
    {
        $packages = ServicePackage::query()
            ->orderBy('price', 'asc')
            ->get(['id', 'name', 'price']);

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    /**
     * Mengambil ketersediaan kuota slot jam pada tanggal tertentu.
     */
    public function slots(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        // Validasi format tanggal (YYYY-MM-DD)
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            $date = Carbon::today()->toDateString();
        }

        // Ambil jumlah kendaraan yang sudah terdaftar per slot jam pada tanggal tersebut
        $bookedCounts = Booking::query()
            ->whereDate('service_date', $date)
            ->select('service_time', DB::raw('count(*) as total'))
            ->groupBy('service_time')
            ->pluck('total', 'service_time')
            ->toArray();

        $slots = [];
        foreach (StoreBookingRequest::AVAILABLE_SLOTS as $slot) {
            $booked = (int) ($bookedCounts[$slot] ?? 0);
            $remaining = max(0, self::MAX_QUOTA_PER_SLOT - $booked);
            $isFull = $booked >= self::MAX_QUOTA_PER_SLOT;

            $slots[] = [
                'time' => $slot,
                'booked' => $booked,
                'max' => self::MAX_QUOTA_PER_SLOT,
                'remaining' => $remaining,
                'is_full' => $isFull,
            ];
        }

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $slots,
        ]);
    }

    /**
     * Mengambil daftar data booking dengan filter pencarian.
     */
    public function bookings(Request $request): JsonResponse
    {
        $query = Booking::with('servicePackage:id,name,price');

        if ($request->filled('date')) {
            $query->whereDate('service_date', (string) $request->query('date'));
        }

        if ($request->filled('time')) {
            $query->where('service_time', (string) $request->query('time'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%")
                    ->orWhere('motorcycle_type', 'like', "%{$search}%");
            });
        }

        $bookings = $query
            ->orderBy('service_date', 'desc')
            ->orderBy('service_time', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $bookings->count(),
            'data' => $bookings,
        ]);
    }

    /**
     * Menyimpan data booking baru dengan validasi kuota slot berbasis transaksi database.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated): JsonResponse {
            // Cek kuota di dalam transaksi untuk mencegah race condition (kunci baris data)
            $currentCount = Booking::query()
                ->whereDate('service_date', $validated['service_date'])
                ->where('service_time', $validated['service_time'])
                ->lockForUpdate()
                ->count();

            if ($currentCount >= self::MAX_QUOTA_PER_SLOT) {
                return response()->json([
                    'success' => false,
                    'message' => "Slot jam {$validated['service_time']} pada tanggal {$validated['service_date']} sudah penuh (maksimal " . self::MAX_QUOTA_PER_SLOT . ' kendaraan). Silakan pilih slot jam lain.',
                    'errors' => [
                        'service_time' => [
                            "Slot jam {$validated['service_time']} sudah mencapai kuota maksimal (" . self::MAX_QUOTA_PER_SLOT . ' kendaraan).',
                        ],
                    ],
                ], 422);
            }

            // Normalisasi nomor plat menjadi huruf kapital dan bersihkan spasi
            $validated['plate_number'] = strtoupper(trim($validated['plate_number']));
            $validated['customer_name'] = trim($validated['customer_name']);
            $validated['motorcycle_type'] = trim($validated['motorcycle_type']);

            $booking = Booking::create($validated);
            $booking->load('servicePackage:id,name,price');

            return response()->json([
                'success' => true,
                'message' => "Pendaftaran servis untuk {$booking->customer_name} ({$booking->plate_number}) berhasil disimpan!",
                'data' => $booking,
            ], 201);
        });
    }
}
