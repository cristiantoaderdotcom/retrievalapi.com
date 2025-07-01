<?php

namespace Database\Seeders;

use App\Models\Referral;
use App\Models\User;
use App\Models\UserPayment;
use App\Models\UserReferral;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralsAndPaymentsSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        // Get all users or create some if none exist
        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory(5)->create();
        }

        // Create referrals for each user
        foreach ($users as $user) {
            // Create 1-3 referrals per user
            $referralCount = rand(1, 3);

            for ($i = 0; $i < $referralCount; $i++) {
                $referral = Referral::create([
                    'user_id' => $user->id,
                    'code' => Str::random(8),
                    'description' => fake()->sentence(),
                    'status' => fake()->randomElement(['active', 'inactive', 'expired']),
                    'clicks' => fake()->numberBetween(10, 200),
                    'commission_rate' => fake()->randomFloat(2, 10, 50),
                    'expires_at' => fake()->boolean(30) ? fake()->dateTimeBetween('+1 month', '+1 year') : null,
                ]);

                // Create 0-10 referrers for each referral
                $referrerCount = rand(0, 10);
                $referrerIds = [];

                // Get random users who aren't the owner of this referral
                $potentialReferrers = $users->where('id', '!=', $user->id);

                for ($j = 0; $j < $referrerCount && $j < $potentialReferrers->count(); $j++) {
                    $referrer = $potentialReferrers->random();

                    // Skip if this user is already a referrer
                    if (in_array($referrer->id, $referrerIds)) {
                        continue;
                    }

                    $referrerIds[] = $referrer->id;

                    // Create the user referral connection
                    UserReferral::create([
                        'user_id' => $referrer->id,
                        'referral_id' => $referral->id,
                    ]);

                    // Create 0-3 payments for each referrer
                    $paymentCount = rand(0, 3);

                    for ($k = 0; $k < $paymentCount; $k++) {
                        UserPayment::create([
                            'user_id' => $referrer->id,
                            'amount_total' => fake()->randomFloat(2, 10, 1000),
                            'status' => fake()->randomElement(['pending', 'paid']),
                            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
                            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
                        ]);
                    }
                }
            }
        }
    }
}