<?php

namespace App\Mail;

use App\Language;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyPendingQuestions extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Language object.
     *
     * @var Language
     */
    public $language;

    /**
     * Count of unhandled PendingQuestions.
     *
     * @var int
     */
    public $count;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Language $language, int $count)
    {
        $this->language = $language;
        $this->count = $count;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.daily-pending-questions');
    }
}
