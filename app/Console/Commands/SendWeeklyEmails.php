<?php

namespace App\Console\Commands;

use App\Language;
use App\Mail\WeeklyPendingQuestions;
use App\PendingQuestion;
use App\States\PendingQuestion\Pending;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sherpa:send-weekly-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all weekly emails';

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
     * Applies special condition to English query that would make sure other languages are disregarded.
     *
     * @param  Collection  $languages
     * @param  Builder  $query
     * @return void
     */
    private function applyEnglishCondition(Collection $languages, Builder &$query)
    {
        $inValue = $languages->filter(function ($language) {
            return $language->code !== 'en';
        })->map(function ($language) {
            return $language->id;
        });
        $query->whereDoesntHave('languages', function (Builder $query) use ($inValue) {
            $query->whereIn('language_id', $inValue);
        });
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $languages = Language::all();

        $languages->each(function ($language) use ($languages) {
            $query = PendingQuestion::whereState('status', Pending::class)
                ->whereHas('languages', function (Builder $query) use ($language) {
                    $query->where('language_id', $language->id);
                });

            // English is a special case that should ignore translations to other languages
            if ($language->code === 'en') {
                $this->applyEnglishCondition($languages, $query);
            }

            $count = $query->count();

            if ($count > 0) {
                $languageExperts = User::role('expert')
                    ->where('language_id', $language->id)
                    ->get();

                $languageExperts->whenNotEmpty(function ($user) use ($language, $count) {
                    Mail::to($user)->send(new WeeklyPendingQuestions($language, $count));
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
