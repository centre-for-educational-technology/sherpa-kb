<?php

namespace App\Console\Commands;

/*
# This should truncate all the data, including pending questions
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE language_pending_question;
TRUNCATE TABLE pending_questions;
TRUNCATE TABLE language_question;
TRUNCATE TABLE questions;
TRUNCATE TABLE answer_language;
TRUNCATE TABLE answers;
SET FOREIGN_KEY_CHECKS = 1;
*/

use App\Answer;
use App\Question;
use App\Services\LanguageService;
use App\States\Answer\Translated as TranslatedAnswer;
use App\States\Question\Translated as TranslatedQuestion;
use App\Topic;
use Illuminate\Console\Command;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sherpa:import-data {language} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import question and answer data from a .csv file.';

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
    public function handle(LanguageService $languageService)
    {
        $languageId = $languageService->getLanguageIdByCode($this->argument('language'));

        if ($languageId === null) {
            $this->error("Could not find a language with code of {$this->argument('language')}!");

            return 1;
        }

        if (! file_exists($this->argument('file'))) {
            $this->error("File {$this->argument('file')} not found!");

            return 1;
        }

        $counts = [
            'answers' => 0,
            'questions' => 0,
        ];
        $data = [];
        $handle = fopen($this->argument('file'), 'r');

        while (($row = fgetcsv($handle)) !== false) {
            $topic = trim($row[0]);
            $question = trim($row[1]);
            $answer = trim($row[2]);

            if (! array_key_exists($answer, $data)) {
                $data[$answer] = [
                    'answer' => $answer,
                    'questions' => [],
                ];
            }
            $data[$answer]['questions'][] = [
                'topic' => $topic,
                'question' => $question,
            ];
        }

        fclose($handle);

        $topics = Topic::all()->keyBy('description');

        foreach ($data as $row) {
            $answer = new Answer;
            $answer->save();
            $answer->languages()->attach([
                $languageId => [
                    'description' => $row['answer'],
                ],
            ]);
            $answer->status->transitionTo(TranslatedAnswer::class);
            $counts['answers']++;

            foreach ($row['questions'] as $single) {
                $topic = $topics->get($single['topic']);

                $question = new Question;
                $question->save();

                if ($topic) {
                    $question->topic()->associate($topic->id)->save();
                }

                $question->answer()->associate($answer->id)->save();
                $question->languages()->attach([
                    $languageId => [
                        'description' => $single['question'],
                    ],
                ]);
                $question->status->transitionTo(TranslatedQuestion::class);
                $counts['questions']++;
            }
        }

        $this->info("Imported {$counts['questions']} questions and {$counts['answers']} answers.");
    }
}
