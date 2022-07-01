<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

use App\Models\Error;

class ErrorNotification extends Notification
{
    // use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Error $error, $section, $subsection)
    {
        $this->error = $error;
        $this->section = $section;
        $this->subsection = $subsection;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $url = url("/errores?q=".$this->error->id);

        return (new SlackMessage)
                ->from('GESTOR', ':computer:')
                ->to('#log_aulas_virtuales')
                ->error()
                ->content('Se encontró un posible error en *' . $this->section .'*')
                ->attachment(function ($attachment) use ($url) {
                    $attachment->title($this->subsection, $url)
                               ->content($this->error->message);
                });
    }
}
