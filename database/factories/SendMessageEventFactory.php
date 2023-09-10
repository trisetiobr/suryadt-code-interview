<?php

namespace Database\Factories;

use App\Models\SendMessageEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SendMessageEventFactory extends Factory
{
    protected $model = SendMessageEvent::class;

    public function definition()
    {
        $locations = \App\Helpers\LocationHelper::locations();
        $length = count($locations);
        $randomIndex = random_int(0, $length - 1);
        $randomLocation = $locations[$randomIndex];

        return [
            'timezone' => $randomLocation
        ];
    }
}
