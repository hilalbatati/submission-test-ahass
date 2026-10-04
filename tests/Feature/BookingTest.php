<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ServicePackage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private ServicePackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->package = ServicePackage::create([
            'name' => 'Servis Rutin (Oli & Filter)',
            'price' => 150000,
        ]);
    }

    public function test_booking_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('AHASS')
            ->assertSee('Pendaftaran Servis');
    }

    public function test_packages_endpoint_returns_packages_list(): void
    {
        ServicePackage::create([
            'name' => 'Tune Up',
            'price' => 120000,
        ]);

        $response = $this->getJson('/packages');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_slots_endpoint_returns_availability(): void
    {
        $today = Carbon::today()->toDateString();

        // Create 2 bookings for 09:00 slot
        Booking::create([
            'plate_number' => 'B 1111 AA',
            'customer_name' => 'Pelanggan 1',
            'motorcycle_type' => 'Beat',
            'service_date' => $today,
            'service_time' => '09:00',
            'service_package_id' => $this->package->id,
        ]);

        Booking::create([
            'plate_number' => 'B 2222 BB',
            'customer_name' => 'Pelanggan 2',
            'motorcycle_type' => 'Vario',
            'service_date' => $today,
            'service_time' => '09:00',
            'service_package_id' => $this->package->id,
        ]);

        $response = $this->getJson("/slots?date={$today}");

        $response->assertOk()
            ->assertJson(['success' => true, 'date' => $today]);

        $data = $response->json('data');
        $this->assertCount(10, $data);

        $slot09 = collect($data)->firstWhere('time', '09:00');
        $this->assertNotNull($slot09);
        $this->assertEquals(2, $slot09['booked']);
        $this->assertEquals(1, $slot09['remaining']);
        $this->assertFalse($slot09['is_full']);

        $slot08 = collect($data)->firstWhere('time', '08:00');
        $this->assertNotNull($slot08);
        $this->assertEquals(0, $slot08['booked']);
        $this->assertEquals(3, $slot08['remaining']);
        $this->assertFalse($slot08['is_full']);
    }

    public function test_customer_can_successfully_create_a_booking(): void
    {
        $today = Carbon::today()->toDateString();

        $payload = [
            'plate_number' => 'b 1234 xyz',
            'customer_name' => 'Ahmad Dahlan',
            'motorcycle_type' => 'Honda PCX 160',
            'service_date' => $today,
            'service_time' => '10:00',
            'service_package_id' => $this->package->id,
        ];

        $response = $this->postJson('/bookings', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'plate_number' => 'B 1234 XYZ',
                    'customer_name' => 'Ahmad Dahlan',
                    'motorcycle_type' => 'Honda PCX 160',
                    'service_time' => '10:00',
                ],
            ]);

        $this->assertDatabaseHas('bookings', [
            'plate_number' => 'B 1234 XYZ',
            'customer_name' => 'Ahmad Dahlan',
            'service_time' => '10:00',
            'service_package_id' => $this->package->id,
        ]);
    }

    public function test_booking_fails_when_slot_quota_exceeds_3_vehicles(): void
    {
        $today = Carbon::today()->toDateString();

        // Fill slot 11:00 with 3 bookings
        for ($i = 1; $i <= 3; $i++) {
            Booking::create([
                'plate_number' => "B {$i}000 TEST",
                'customer_name' => "Pelanggan {$i}",
                'motorcycle_type' => 'Honda Beat',
                'service_date' => $today,
                'service_time' => '11:00',
                'service_package_id' => $this->package->id,
            ]);
        }

        // Attempt to book 4th vehicle in same slot
        $payload = [
            'plate_number' => 'B 4000 FULL',
            'customer_name' => 'Pelanggan 4',
            'motorcycle_type' => 'Honda Vario',
            'service_date' => $today,
            'service_time' => '11:00',
            'service_package_id' => $this->package->id,
        ];

        $response = $this->postJson('/bookings', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['service_time']);

        $this->assertDatabaseMissing('bookings', [
            'plate_number' => 'B 4000 FULL',
        ]);
    }

    public function test_booking_fails_with_past_service_date(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $payload = [
            'plate_number' => 'B 9999 PAS',
            'customer_name' => 'Pelanggan Kemarin',
            'motorcycle_type' => 'Honda Scoopy',
            'service_date' => $yesterday,
            'service_time' => '08:00',
            'service_package_id' => $this->package->id,
        ];

        $response = $this->postJson('/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_date']);
    }

    public function test_booking_fails_with_invalid_service_time(): void
    {
        $today = Carbon::today()->toDateString();

        $payload = [
            'plate_number' => 'B 9999 INV',
            'customer_name' => 'Pelanggan Malam',
            'motorcycle_type' => 'Honda Supra',
            'service_date' => $today,
            'service_time' => '19:00', // Outside 08:00 - 17:00
            'service_package_id' => $this->package->id,
        ];

        $response = $this->postJson('/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_time']);
    }

    public function test_booking_fails_with_invalid_service_package(): void
    {
        $today = Carbon::today()->toDateString();

        $payload = [
            'plate_number' => 'B 9999 NPK',
            'customer_name' => 'Pelanggan Paket Salah',
            'motorcycle_type' => 'Honda CBR',
            'service_date' => $today,
            'service_time' => '08:00',
            'service_package_id' => 99999,
        ];

        $response = $this->postJson('/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_package_id']);
    }

    public function test_bookings_list_can_be_filtered(): void
    {
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        Booking::create([
            'plate_number' => 'B 1111 ONE',
            'customer_name' => 'Andi Wijaya',
            'motorcycle_type' => 'Honda Beat',
            'service_date' => $today,
            'service_time' => '08:00',
            'service_package_id' => $this->package->id,
        ]);

        Booking::create([
            'plate_number' => 'D 2222 TWO',
            'customer_name' => 'Bambang Sudirman',
            'motorcycle_type' => 'Honda Vario',
            'service_date' => $tomorrow,
            'service_time' => '14:00',
            'service_package_id' => $this->package->id,
        ]);

        // Filter by date
        $resDate = $this->getJson("/bookings?date={$today}");
        $resDate->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('B 1111 ONE', $resDate->json('data.0.plate_number'));

        // Filter by time
        $resTime = $this->getJson('/bookings?time=14:00');
        $resTime->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('D 2222 TWO', $resTime->json('data.0.plate_number'));

        // Filter by search customer name
        $resSearch = $this->getJson('/bookings?search=Bambang');
        $resSearch->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('Bambang Sudirman', $resSearch->json('data.0.customer_name'));

        // Filter by search plate
        $resSearchPlate = $this->getJson('/bookings?search=1111');
        $resSearchPlate->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('B 1111 ONE', $resSearchPlate->json('data.0.plate_number'));
    }
}
