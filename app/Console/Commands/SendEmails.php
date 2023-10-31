<?php

namespace App\Console\Commands;

use App\Mail\EmailTemplate;
use App\Models\Mongo\EmailLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Emails every 5 minutes by command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->sendEmails();
    }

    private function sendEmails(){
        $emails_log = EmailLog::where('status','no_sent')->limit(15)->get();
        $bar = $this->output->createProgressBar(count($emails_log));
        $bar->start();
        foreach ($emails_log as $email_log) {
            try {
                Mail::to($email_log['user_email'])->send(new EmailTemplate($email_log['template'], $email_log['email_data']));
                $email_log->status = 'sent';
            } catch (\Throwable $th) {
                $email_log->status = 'error';
            }
            $email_log->save();
            $bar->advance();
        }
        $bar->finish();
    }
}
