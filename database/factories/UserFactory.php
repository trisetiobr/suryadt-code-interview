<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        $currentDate = Carbon::now();
        $formattedRandomDate = $currentDate->format('Y-m-d');

        $locations = \App\Helpers\LocationHelper::locations();
        $length = count($locations);
        $randomIndex = random_int(0, $length - 1);
        $randomLocation = $locations[$randomIndex];

        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'date_of_birth' => $formattedRandomDate,
            'location' => $randomLocation
        ];
    }
}
