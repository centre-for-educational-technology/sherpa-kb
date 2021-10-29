<?php

namespace Tests\Unit;

use App\Listeners\LogSuccessfulLogout;
use App\User;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LogSuccessfulLogoutTest extends TestCase
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
            Logout::class,
        ]);
        Event::assertListening(
            Logout::class,
            LogSuccessfulLogout::class
        );
    }

    /**
     * Tests that logout event is logged into activity_log table.
     *
     * @returns void
     */
    public function test_it_logs_event()
    {
        $user = User::factory()->create();
        $event = new Logout('web', $user);
        $listener = new LogSuccessfulLogout();
        $listener->handle($event);

        $this->assertDatabaseCount('activity_log', 2);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'logout',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => User::class,
            'properties' => '{"guard":"web"}',
        ]);
    }
}
