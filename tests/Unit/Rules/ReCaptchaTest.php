<?php

namespace Tests\Unit\Rules;

use App\Rules\ReCaptcha;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class ReCaptchaTest extends TestCase
{
    const RECAPTCHA_ACTION = 'suggest';
    const RECAPTCHA_RESPONSE = 'fake';

    /**
     * @var ReCaptcha
     */
    protected $rule;

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ReCaptcha(self::RECAPTCHA_ACTION, 0.5);
    }

    protected function mockHttpRequest(bool $successful = true, bool $success = true, float $score = 1.0, string $action = self::RECAPTCHA_ACTION)
    {
        Http::shouldReceive('asForm')
            ->once()
            ->andReturnUsing(function () use ($successful, $success, $score, $action) {
                $pendingRequest = Mockery::mock(PendingRequest::class);

                $pendingRequest->shouldReceive('post')
                    ->once()
                    ->with(ReCaptcha::ENDPOINT, [
                        'secret' => config('services.recaptcha.secret'),
                        'response' => self::RECAPTCHA_RESPONSE,
                        'remoteip' => '127.0.0.1',
                    ])
                    ->andReturnUsing(function () use ($successful, $success, $score, $action) {
                        $response = Mockery::mock(Response::class);

                        $response->shouldReceive('successful')
                            ->once()
                            ->andReturn($successful);

                        $response->shouldReceive('json')
                            ->times($successful ? 1 : 0)
                            ->andReturn([
                                'success' => $success,
                                'score' => $score,
                                'action' => $action,
                                'challenge_ts' => Carbon::now()->toIso8601String(), // yyyy-MM-dd'T'HH:mm:ssZZ (this is close enough)
                                'hostname' => '127.0.0.1',
                            ]);

                        return $response;
                    });

                return $pendingRequest;
            });
    }

    public function test_has_correct_endpoint()
    {
        $this->assertEquals('https://www.google.com/recaptcha/api/siteverify', $this->rule::ENDPOINT);
    }

    public function test_recaptcha_pass()
    {
        $this->mockHttpRequest();
        $this->assertTrue($this->rule->passes('attribute', self::RECAPTCHA_RESPONSE));
        $this->assertEquals('', $this->rule->message());
    }

    public function test_recaptcha_response_not_successful()
    {
        $this->mockHttpRequest(false);
        $this->assertFalse($this->rule->passes('attribute', self::RECAPTCHA_RESPONSE));
        $this->assertEquals('reCAPTCHA service error', $this->rule->message());
    }

    public function test_recaptcha_response_not_success()
    {
        $this->mockHttpRequest(true, false);
        $this->assertFalse($this->rule->passes('attribute', self::RECAPTCHA_RESPONSE));
        $this->assertEquals('reCAPTCHA failed request', $this->rule->message());
    }

    public function test_recaptcha_response_wrong_action()
    {
        $this->mockHttpRequest(true, true, 1.0, 'fake');
        $this->assertFalse($this->rule->passes('attribute', self::RECAPTCHA_RESPONSE));
        $this->assertEquals('reCAPTCHA wrong action', $this->rule->message());
    }

    public function test_recaptcha_response_score_too_low()
    {
        $this->mockHttpRequest(true, true, 0.2);
        $this->assertFalse($this->rule->passes('attribute', self::RECAPTCHA_RESPONSE));
        $this->assertEquals('reCAPTCHA score too low', $this->rule->message());
    }
}