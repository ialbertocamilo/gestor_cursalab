<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;


class EmailTemplate extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;
    public $view;
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($view, $data)
    {
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->view, ['data' => $this->data ])
            ->text($this->view,  ['data' => $this->data ])
            ->subject($this->data['subject'])
            ->mailersend(null);
    }
}
