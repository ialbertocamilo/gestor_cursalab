<?php

namespace App\Console\Commands;

use App\Models\Taxonomy;
use App\Models\EmailUser;
use App\Models\Workspace;
use App\Mail\EmailTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmailApisInformation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-email:apis-information';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía correos informativos sobre los procesos de creación,actualización y ceses de usuarios por apis';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       $this->sendEmail();
    }

    private function sendEmail(){
        $type_id = Taxonomy::where('group','email')->where('type','user')->where('code','info_apis')->first()?->id;
        // $users_to_send_email = EmailUser::with(['user:id,email_gestor','workspace:id,name'])->wherehas('user',function($q){
        //     $q->where('active',ACTIVE);
        // })->where('type_id',$type_id)->get();
        // $reports = GeneratedReport::query()
        // ->where('workspace_id', $workspaceId)
        // ->where('created_at', '>=', Carbon::today()->toDateTimeString())
        // ->get('created_at', 'desc');
        // foreach ($users_to_send_email as $users_to_send) {
        //     $mail_data=[
        //         'subject'=>'Reporte de APIS',
        //         'init_date'=> date('d/m/Y', strtotime('-1 day')). ' 6:00 am',
        //         'final_date'=> date('d/m/Y').' 5:30 am',
        //         'route' => env('REPORTS_BASE_URL').'/reports/1683154596242.xlsx'
        //     ];
        //     Mail::to($users_to_send->user->email_gestor)
        //     ->send(new EmailTemplate('emails.email_information_apis', $mail_data));
        // }
        $workspaces = Workspace::with('emails.user:id,email_gestor')->wherehas('emails.user',function($q){
            $q->where('active',ACTIVE);
        })->select('id','name')->get();
        foreach ($workspaces as $workspace) {
            $emais_to_send_user = $workspace->emails->where('type_id',$type_id)->map( fn ($e)=> $e->user->email_gestor);
            if(count($emais_to_send_user) == 0){
                continue;
            }
            $mail_data=[
                'subject'=>'Reporte de actualización automática de datos de usuarios',
                'init_date'=> date('d/m/Y', strtotime('-1 day')). ' 6:00 am',
                'final_date'=> date('d/m/Y').' 5:30 am',
                'route' => env('REPORTS_BASE_URL').'/reports/1683154596242.xlsx'
            ];
            dd($emais_to_send_user);
            Mail::to($emais_to_send_user)
            ->send(new EmailTemplate('emails.email_information_apis', $mail_data));
        }
    }
}
