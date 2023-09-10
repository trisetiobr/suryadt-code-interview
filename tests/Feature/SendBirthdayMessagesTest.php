<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use DateTimeZone;
use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\SendMessageEvent;
use Illuminate\Support\Facades\Http;

class SendBirthdayMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2023-09-01 00:00:00'));
    }

    public function testSendBirthdayMessagesCommand()
    {
        Http::fake([
            env('EMAIL_SERVICE_BASE_URL') . '/send-email' => Http::response([], 200),
        ]);

        $user1 = User::factory()->create([
            'location' => 'Asia/Tokyo',
        ]);
        $user2 = User::factory()->create([
            'location' => 'Europe/London',
        ]);
        $desiredDate = '09:00';

        $currentTimezoneDate = Carbon::now(new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i');

        $this->artisan("birthday:send {$desiredDate}")
            ->expectsOutput("Birthday message sent to {$user1->first_name} {$user1->last_name}")
            ->assertExitCode(0);
        $this->assertEquals(SendMessageEvent::where('status', 'success')->count(), 1);
        $this->assertEquals(SendMessageEvent::where('status', '!=', 'success')->count(), 0);
    }

    public function testSendBirthdayMessageWithFailedResponse()
    {
        Http::fake([
            env('EMAIL_SERVICE_BASE_URL') . '/send-email' => Http::response([], 500),
        ]);

        $user1 = User::factory()->create([
            'location' => 'Asia/Tokyo',
        ]);
        $user2 = User::factory()->create([
            'location' => 'Europe/London',
        ]);
        $user3 = User::factory()->create([
            'location' => 'Asia/Tokyo',
        ]);
        $desiredDate = '09:00';

        $this->artisan("birthday:send {$desiredDate}")
            ->expectsOutput("Failed to send birthday message to {$user1->first_name} {$user1->last_name}");
        $this->assertEquals(SendMessageEvent::where('status', '!=', 'failed')->count(), 0);
        $this->assertEquals(SendMessageEvent::where('status', '=', 'failed')->count(), 2);
    }

    public function testSendBirthdayMessageWithRetryMechanism()
    {
        Http::fake([
            env('EMAIL_SERVICE_BASE_URL') . '/send-email' => Http::response([], 200),
        ]);
        $desiredDate = '09:00';
        $user1 = User::factory()->create([
            'location' => 'Asia/Tokyo',
        ]);
        $user2 = User::factory()->create([
            'location' => 'Europe/London',
        ]);
        $user3 = User::factory()->create([
            'location' => 'Asia/Tokyo',
        ]);
        $user4 = User::factory()->create([
            'location' => 'Asia/Jakarta',
        ]);
        $user5 = User::factory()->create([
            'location' => 'Asia/Jakarta',
        ]);

        // this will be retry
        $eventFailedSameDay = SendMessageEvent::factory()->create([
            'status' => 'failed',
            'created_at' => '00:10',
            'user_id' => $user4->id,
            'errors' => 'an error occur',
            'event_type' => 'birthday',
            'timezone' => $user4->location
        ]);
        // this not be retry
        $eventFailedDifferentDay = SendMessageEvent::factory()->create([
            'status' => 'failed',
            'created_at' => Carbon::now()->subDays('1')->format('Y-m-d') . ' 00:10',
            'user_id' => $user4->id,
            'errors' => 'an error occur',
            'event_type' => 'birthday',
            'timezone' => $user4->location
        ]);
        $this->artisan("birthday:send {$desiredDate}")
            ->expectsOutput("Birthday message sent to {$user1->first_name} {$user1->last_name}")
            ->assertExitCode(0);
        $this->assertEquals(SendMessageEvent::where('status', '=', 'failed')->count(), 1);
        $this->assertEquals(SendMessageEvent::where('status', '=', 'success')->count(), 3);
    }
}
