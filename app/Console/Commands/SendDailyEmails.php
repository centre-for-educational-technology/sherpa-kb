<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Language;
use App\PendingQuestion;
use App\States\PendingQuestion\Pending;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyPendingQuestions;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class SendDailyEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sherpa:send-daily-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all daily emails';

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
        $languages = Language::all();

        $languages->each(function($language) {
            $count = PendingQuestion::whereState('status', Pending::class)
                ->whereColumn('created_at', 'updated_at')
                ->whereDate('created_at', Carbon::yesterday())
                ->whereHas('languages', function(Builder $query) use ($language) {
                    $query->where('language_id', $language->id);
                })
                ->count();

            if ($count > 0) {
                $languageExperts = User::role('expert')
                    ->where('language_id', $language->id)
                    ->get();

                $languageExperts->whenNotEmpty(function($user) use ($language, $count) {
                    Mail::to($user)->send(new DailyPendingQuestions($language, $count));
                });

                if ($languageExperts->isEmpty()) {
                    Log::debug('No suitable language experts could be found!', [
                        'language' => $language->name,
                        'count' => $count,
                        'command' => self::class,
                    ]);
                }
            }
        });

        return 0;
    }
}
