<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DateTimeZone;
use App\Models\User;

class SendBirthdayMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:send {desired_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday messages to users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        $desiredDate = $this->argument('desired_date');

        $retryIds = \App\Helpers\BirthdateHelper::failedSendToday($currentDate);
        $excludeIds = \App\Helpers\BirthdateHelper::successSendToday($currentDate);
        $timezones = \App\Helpers\BirthdateHelper::getTimezones($desiredDate);

        if (!empty($timezones) || !empty($retryIds)) {
            $users = User::where(function ($query) use ($timezones, $retryIds) {
                $query->whereIn('id', $retryIds)
                      ->orWhereIn('location', $timezones);
            })->whereNotIn('id', $excludeIds)->get();

            foreach ($users as $user) {
                $response = \App\Helpers\BirthdateHelper::sendBirthdayEmail($user);

                if ($response->successful()) {
                    $this->info("Birthday message sent to $user->first_name $user->last_name");
                } else {
                    $this->error("Failed to send birthday message to $user->first_name $user->last_name");
                }
            }
        }
    }
}
