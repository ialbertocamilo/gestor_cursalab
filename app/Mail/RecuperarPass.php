<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecuperarPass extends Mailable
{
    use Queueable, SerializesModels;

    public $random;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($random)
    {
        //
        $this->random = $random;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.recuperar_pass')
                    ->subject('CursaLab - Recuperar Contraseña - '.date('d/m/Y'));
    }
}
