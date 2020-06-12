<?php

namespace App\Mail;

use App\Litter;
use App\LitterApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class LitterRequestReplied extends Mailable
{
    use Queueable, SerializesModels;

    /** @var Litter */
    protected $litter;

    /** @var LitterApprovalRequest */
    protected $litterApprovalRequest;

    /**
     * Create a new message instance.
     *
     * @param Model $litter
     * @param Model $litterApprovalRequest
     */
    public function __construct(Model $litter, Model $litterApprovalRequest)
    {
        $this->litter = $litter;
        $this->litterApprovalRequest = $litterApprovalRequest;
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
        $state = __('messages.' . Str::lower($this->litterApprovalRequest->state));
        $subject = __('messages.litter_request_state_change', ['litter' => $this->litter->label, 'state' => $state ]);
        $replyTo = 'noreply@czkp.cz';


        return $this->view('emails.litter_requests.replied')
            ->from($from, $name)
            ->replyTo($replyTo, $name)
            ->subject($subject)
            ->with([
                'litterName' => $this->litter->label,
                'litterUrl' => $this->getLitterUrl(),
                'registratorName' => $this->litterApprovalRequest->registrator->name,
                'state' => $state
            ]);
    }
}
