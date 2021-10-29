<?php

namespace Tests\Unit;

use App\Listeners\LogSuccessfulLogin;
use App\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LogSuccessfulLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests that event listener is attached to an event.
     *
     * @return void
     */
    public function test_is_attached_to_event()
    {
        Event::fake([
            Login::class,
        ]);
        Event::assertListening(
            Login::class,
            LogSuccessfulLogin::class
        );
    }

    /**
     * Tests that login event is logged into activity_log table.
     *
     * @returns void
     */
    public function test_it_logs_event()
    {
        $user = User::factory()->create();
        $event = new Login('web', $user, false);
        $listener = new LogSuccessfulLogin();
        $listener->handle($event);

        $this->assertDatabaseCount('activity_log', 2);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'login',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => User::class,
            'properties' => '{"guard":"web","remember":false}',
        ]);
    }
}
