<?php

namespace Tests\Feature;

use App\Console\Commands\SendDailyEmails;
use App\Console\Commands\SendWeeklyEmails;
use App\Language;
use App\Mail\DailyPendingQuestions;
use App\Mail\WeeklyPendingQuestions;
use App\PendingQuestion;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ConsoleCommandsTest extends KnowledgeBaseTestCase
{

    /**
     * Creates a PendingQuestion with timestamps set to yesterday and a text in English.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    private function createPendingQuestion()
    {
        $english = Language::where('code', 'en')->first();

        return PendingQuestion::factory([
            'created_at' => Carbon::yesterday(),
            'updated_at' => Carbon::yesterday(),
        ])
            ->hasAttached($english, ['description' => 'Description'])
            ->create();
    }

    /**
     * Test the auth:create-admin command.
     *
     * @return void
     */
    public function test_create_admin_command()
    {
        $this->artisan('auth:create-admin Administrator administrator@localhost password')
            ->expectsOutput('Created an account for Administrator')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'administrator@localhost',
        ]);

        $user = User::find(1);

        $this->assertEquals('Administrator', $user->name);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertEquals(1, $user->roles->count());
        $this->assertTrue($user->isAdministrator());
    }

    /**
     * Test the permission:make-administrator command.
     *
     * @return void
     */
    public function test_make_administrator_command()
    {
        $user = $this->createUser();

        $this->artisan('permission:make-administrator 1')
            ->expectsOutput("Administrator role was assigned to a user {$user->name}")
            ->assertExitCode(0);

        $this->assertTrue($user->isAdministrator());

        $this->artisan('permission:make-administrator 1')
            ->expectsOutput("User {$user->name} already has an administrator role!")
            ->assertExitCode(0);

        $this->artisan('permission:make-administrator 2')
            ->expectsOutput('Could not find a user with identifier of 2!')
            ->assertExitCode(1);
    }

    /**
     * Test the sherpa:import-data command.
     *
     * @return void
     */
    public function test_import_data_command()
    {
        $this->artisan(sprintf('sherpa:import-data zz %s', base_path('tests/data/sample_import_data.csv')))
            ->expectsOutput('Could not find a language with code of zz!')
            ->assertExitCode(1);

        $this->artisan(sprintf('sherpa:import-data en %s', 'does-not-exist.csv'))
            ->expectsOutput('File does-not-exist.csv not found!')
            ->assertExitCode(1);

        $this->artisan(sprintf('sherpa:import-data en %s', base_path('tests/data/sample_import_data.csv')))
            ->expectsOutput('Imported 10 questions and 4 answers.')
            ->assertExitCode(0);
    }

    /**
     * Test the sherpa:send-daily-emails command.
     *
     * @return void
     */
    public function test_send_daily_emails_command()
    {
        Log::spy();
        Mail::fake();

        $pendingQuestion = $this->createPendingQuestion();

        Log::shouldReceive('debug')->once()->with('No suitable language experts could be found!', [
            'language' => $pendingQuestion->languages->first()->name,
            'count' => 1,
            'command' => SendDailyEmails::class,
        ]);

        $this->artisan('sherpa:send-daily-emails')
            ->assertExitCode(0);

        Mail::assertNothingSent();

        $this->createLanguageExpert();
        $this->artisan('sherpa:send-daily-emails')
            ->assertExitCode(0);

        Mail::assertSent(DailyPendingQuestions::class);
    }

    /**
     * Test the sherpa:send-weekly-emails command.
     *
     * @return void
     */
    public function test_send_weekly_emails_command()
    {
        Log::spy();
        Mail::fake();

        $pendingQuestion = $this->createPendingQuestion();

        Log::shouldReceive('debug')->once()->with('No suitable language experts could be found!', [
            'language' => $pendingQuestion->languages->first()->name,
            'count' => 1,
            'command' => SendWeeklyEmails::class,
        ]);

        $this->artisan('sherpa:send-weekly-emails')
            ->assertExitCode(0);

        Mail::assertNothingSent();

        $this->createLanguageExpert();

        $this->artisan('sherpa:send-weekly-emails')
            ->assertExitCode(0);

        Mail::assertSent(WeeklyPendingQuestions::class);
    }
}
