<?php

namespace App\Helpers;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\Http;
use \App\Models\SendMessageEvent;

class BirthdateHelper
{
    const DEFAULT_TOLERANCE_MINUTES = 3;
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    public static function failedSendToday($src)
    {
        return SendMessageEvent::where('created_at', '>=', $src . ' 00:00:00')
                                ->where('created_at', '<=', $src . ' 23:59:59')
                                ->where('status', self::STATUS_FAILED)
                                ->pluck('user_id');
    }

    public static function successSendToday($src)
    {
        return SendMessageEvent::where('created_at', '>=', $src . ' 00:00:00')
                                ->where('created_at', '<=', $src . ' 23:59:59')
                                ->where('status', self::STATUS_SUCCESS)
                                ->pluck('user_id');
    }

    public static function sendBirthdayEmail($user)
    {
        $message = "Hey, {$user->first_name} {$user->last_name} it's your birthday";

        $response = Http::post(env('EMAIL_SERVICE_BASE_URL') . '/send-email', [
            'email' => $user->email,
            'message' => $message,
        ]);
        if ($response->status() == 500) {
            $status = self::STATUS_FAILED;
        } else {
            $status = self::STATUS_SUCCESS;
        }
        $currentTimezoneDate = Carbon::now(new DateTimeZone($user->location))->format('Y-m-d');
        $event = SendMessageEvent::where('user_id', $user->id)
                                ->where('created_at', '>=', $currentTimezoneDate . ' 00:00:00')
                                ->where('created_at', '<=', $currentTimezoneDate . ' 23:59:59')
                                ->first();

        if (empty($event->id)) {
            $event = new SendMessageEvent;
        }
        $event->user_id = $user->id;
        $event->event_type = 'birthday';
        $event->timezone = $user->location;
        $event->status = $status;
        if ($status == 'failed') {
            $event->errors = 'an error occurs';
        }
        $event->save();

        return $response;
    }

    public static function getTimezones($desiredTime, $ranges = 0)
    {
        $validTimezones = [];
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        foreach ($timezones as $timezoneName) {
            // Create a Carbon instance for the specified timezone
            $now = Carbon::now(new DateTimeZone($timezoneName));
            if ($ranges === 0) {
                $upperBound = Carbon::parse($desiredTime)->addMinutes(self::DEFAULT_TOLERANCE_MINUTES);
                $bottomBound = Carbon::parse($desiredTime)->subMinutes(self::DEFAULT_TOLERANCE_MINUTES);
            } else {
                $upperBound = Carbon::parse($ranges)->addMinutes($ranges);
                $bottomBound = Carbon::parse($desiredTime);
            }

            $unixTimestampNow = strtotime($now->format('Y-m-d H:i:00'));
            $unixTimestampBottom = strtotime($bottomBound->format('Y-m-d H:i:00'));
            $unixTimestampUpper = strtotime($upperBound->format('Y-m-d H:i:00'));
            if ($unixTimestampNow >= $unixTimestampBottom && $unixTimestampNow <= $unixTimestampUpper) {
                $validTimezones[] = $timezoneName;
            }
        }
        return $validTimezones;
    }
}
