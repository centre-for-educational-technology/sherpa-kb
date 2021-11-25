<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class ReCaptcha implements Rule
{
    const ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    private $message = '';

    private $action;

    private $score;

    /**
     * Create a new rule instance.
     *
     * @param  string  $action
     * @param  float  $score
     */
    public function __construct(string $action, float $score)
    {
        $this->action = $action;
        $this->score = $score;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $response = Http::asForm()->post(self::ENDPOINT, [
            'secret' => config('services.recaptcha.secret'),
            'response' => $value,
            'remoteip' => Request::ip(),
        ]);

        if (! $response->successful()) {
            $this->message = 'reCAPTCHA service error';

            return false;
        }

        $responseData = $response->json();

        if ($responseData['success'] !== true) {
            $this->message = 'reCAPTCHA failed request';

            return false;
        }

        if ($responseData['action'] !== $this->action) {
            $this->message = 'reCAPTCHA wrong action';

            return false;
        }

        if ($responseData['score'] < $this->score) {
            $this->message = 'reCAPTCHA score too low';

            return false;
        }

        // TODO See if there is a need to also check challenge_ts and hostname

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
}
