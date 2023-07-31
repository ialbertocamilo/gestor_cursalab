<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Taxonomy;
use App\Models\EmailUser;
use App\Models\Workspace;
use App\Mail\EmailTemplate;
use App\Models\GeneratedReport;
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
        
        $users_to_send_email = User::whereHas('emails_user')->select('id','name','email_gestor')
                                    ->with(['emails_user','emails_user.workspace:id,name'])
                                    ->whereNotNull('email_gestor')->where('active',ACTIVE)
                                    ->get();

        $current_date = date("Y-m-d");;
        $reports = GeneratedReport::where('report_type','api_information')
                                    ->where('download_url', 'like', '%'.$current_date.'%')
                                    ->orderBy('created_at','DESC')
                                    ->get();

        foreach ($users_to_send_email as $users_to_send) {
            if($users_to_send->roles()->first()?->name == 'super-user'){
                $report = $reports->where('name','general_report')->first();
                $workspaces_to_send_info = [];
                $workspaces_to_send_info[] = [
                    'workspace_name' => 'Descargar reporte',
                    'download_url' => env('REPORTS_BASE_URL').'/'.$report?->download_url,
                ];
            }else{
                $workspaces_to_send_info = $users_to_send->emails_user->where('type_id',$type_id)->filter(function($email_user) use ($reports){
                    $report = $reports->where('name','workspace_report')->where('workspace_id',$email_user->workspace_id)->first();
                    return $report;
                })->map(function($email_user) use ($reports){
                    $report = $reports->where('name','workspace_report')->where('workspace_id',$email_user->workspace_id)->first();
                    return [
                        'workspace_name' => $email_user->workspace->name,
                        'download_url' => env('REPORTS_BASE_URL').'/'.$report?->download_url,
                    ];
                })->toArray();
            }
            if(count($workspaces_to_send_info) == 0){
                continue;
            }
            $mail_data=[
                'subject'=>'Reporte de APIS',
                'init_date'=> date('d/m/Y', strtotime('-1 day')). ' 6:00 am',
                'final_date'=> date('d/m/Y').' 6:00 am',
                'workspaces' => $workspaces_to_send_info
            ];
            Mail::to($users_to_send->email_gestor)
            ->send(new EmailTemplate('emails.email_information_apis', $mail_data));
            sleep(4);
        }
        // $workspaces = Workspace::with('emails.user:id,email_gestor')->wherehas('emails.user',function($q){
        //     $q->where('active',ACTIVE);
        // })->select('id','name')->get();
        // foreach ($workspaces as $workspace) {
        //     $emais_to_send_user = $workspace->emails->where('type_id',$type_id)->map( fn ($e)=> $e->user->email_gestor);
        //     if(count($emais_to_send_user) == 0){
        //         continue;
        //     }
        //     $mail_data=[
        //         'subject'=>'Reporte de actualización automática de datos de usuarios',
        //         'init_date'=> date('d/m/Y', strtotime('-1 day')). ' 6:00 am',
        //         'final_date'=> date('d/m/Y').' 5:30 am',
        //         'route' => env('REPORTS_BASE_URL').'/reports/1683154596242.xlsx'
        //     ];
        //     dd($emais_to_send_user);
        //     Mail::to($emais_to_send_user)
        //     ->send(new EmailTemplate('emails.email_information_apis', $mail_data));
        // }
    }
}
