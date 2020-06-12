<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LitterRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $litter;
    protected $requestedBy;

    /**
     * Create a new message instance.
     *
     * @param Model $litter
     * @param string $requestedBy
     */
    public function __construct(Model $litter, string $requestedBy)
    {
        $this->litter = $litter;
        $this->requestedBy = $requestedBy;
    }

    /**
     * Generate litter's URL
     *
     * @return string
     */
    private function getLitterUrl() {
        return config('app.url') . '/litters/' . $this->litter->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = config('mail.from.address');
        $name = config('mail.from.name');
        $subject = __('messages.new_litter_approval_request');
        $replyTo = 'noreply@czkp.cz';


        return $this->view('emails.litter_requests.created')
            ->from($from, $name)
            ->replyTo($replyTo, $name)
            ->subject($subject)
            ->with([
                'litterName' => $this->litter->label,
                'requestedBy' => $this->requestedBy,
                'litterUrl' => $this->getLitterUrl()
            ]);
    }
}
