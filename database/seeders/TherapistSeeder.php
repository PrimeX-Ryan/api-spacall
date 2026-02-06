<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Provider;
use App\Models\TherapistProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TherapistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $therapists = [
            ['first' => 'John', 'middle' => 'Quincy', 'last' => 'Doe', 'spec' => ['Swedish', 'Deep Tissue'], 'available' => true],
            ['first' => 'Maria', 'middle' => 'Santos', 'last' => 'Garcia', 'spec' => ['Prenatal', 'Aromatherapy'], 'available' => true],
            ['first' => 'Robert', 'middle' => 'Lee', 'last' => 'Smith', 'spec' => ['Sports Massage', 'Reflexology'], 'available' => true],
            ['first' => 'Elena', 'middle' => 'Cruz', 'last' => 'Perez', 'spec' => ['Facial', 'Hot Stone'], 'available' => true],
            ['first' => 'David', 'middle' => 'Tan', 'last' => 'Ang', 'spec' => ['Thai Massage', 'Shiatsu'], 'available' => true],
            ['first' => 'Sarah', 'middle' => 'Jane', 'last' => 'Miller', 'spec' => ['Clinical Massage'], 'available' => false],
            ['first' => 'Michael', 'middle' => 'A.', 'last' => 'Wilson', 'spec' => ['Physiotherapy'], 'available' => false],
            ['first' => 'Lisa', 'middle' => 'M.', 'last' => 'Brown', 'spec' => ['Reiki'], 'available' => false],
            ['first' => 'James', 'middle' => 'B.', 'last' => 'Davis', 'spec' => ['Trigger Point'], 'available' => false],
            ['first' => 'Emily', 'middle' => 'C.', 'last' => 'White', 'spec' => ['Lomi Lomi'], 'available' => false],
        ];

        foreach ($therapists as $index => $t) {
            // Create User
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'mobile_number' => '09' . str_pad($index + 10, 9, '0', STR_PAD_LEFT),
                'first_name' => $t['first'],
                'middle_name' => $t['middle'],
                'last_name' => $t['last'],
                'gender' => ($index % 2 == 0) ? 'male' : 'female',
                'date_of_birth' => '1990-01-01',
                'pin_hash' => Hash::make('123456'),
                'is_verified' => true,
                'role' => 'therapist',
            ]);

            // Create Provider
            $provider = Provider::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'type' => 'therapist',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'commission_rate' => 15.00,
                'average_rating' => 4.5 + (rand(0, 5) / 10),
                'total_reviews' => rand(10, 100),
                'is_active' => true,
                'is_available' => $t['available'],
                'is_accepting_bookings' => true,
                'accepts_home_service' => true,
                'accepts_store_service' => false,
            ]);

            // Create Therapist Profile
            TherapistProfile::create([
                'provider_id' => $provider->id,
                'specializations' => $t['spec'],
                'bio' => "Professional therapist specialized in " . implode(', ', $t['spec']) . ".",
                'years_of_experience' => rand(2, 15),
                'certifications' => ['Certified Massage Therapist', 'Health & Safety Certified'],
                'languages_spoken' => ['English', 'Filipino'],
                'license_number' => 'TRP-' . rand(1000, 9999),
                'license_type' => 'Professional',
                'base_rate' => rand(500, 1500),
                'service_radius_km' => 10,
                'base_location_latitude' => 14.5 + (rand(0, 100) / 1000),
                'base_location_longitude' => 120.9 + (rand(0, 100) / 1000),
                'base_address' => 'Sample Address ' . ($index + 1),
                'default_schedule' => [
                    'monday' => ['09:00', '18:00'],
                    'tuesday' => ['09:00', '18:00'],
                    'wednesday' => ['09:00', '18:00'],
                    'thursday' => ['09:00', '18:00'],
                    'friday' => ['09:00', '18:00'],
                ],
            ]);

            // Assign services to therapist
            $services = \App\Models\Service::inRandomOrder()->take(rand(2, 4))->get();
            foreach ($services as $service) {
                $provider->services()->attach($service->id, [
                    'price' => $service->base_price + rand(-100, 200),
                    'is_available' => true
                ]);
            }
        }
    }
}
