<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MailerSend\LaravelDriver\MailerSendTrait;

class SendEmailSupportLogin extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected $usuario;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $to = Arr::get($this->to, '0.address');

        return $this->view('emails.soporte_login', ['usuario' => $this->usuario])
            ->text('emails.soporte_login', ['usuario' => $this->usuario])
            ->subject('Support Login')
            ->mailersend(null);
    }
}
