<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

use App\Models\Error;

class ErrorExceptionNotification extends Notification
{
    // use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Error $error, $notifier)
    {
        $this->error = $error;
        $this->notifier = $notifier;
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
        // info('notifiable');
        // info($notifiable);

        $url = url("/errores?q=".$this->error->id);

        return (new SlackMessage)
                ->from('GESTOR', ':computer:')
                ->to('#log_aulas_virtuales')
                ->error()
                ->content('*' . $this->notifier . '* se encontró con un error en *' . $this->error->url .'*')
                ->attachment(function ($attachment) use ($url) {
                    $attachment->title('Exception Error found in ' . $this->error->file . ' at line ' . $this->error->line, $url)
                               ->content($this->error->message);
                });
    }
}
