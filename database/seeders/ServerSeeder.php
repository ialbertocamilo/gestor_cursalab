<?php

namespace Database\Seeders;

use App\Models\Server;
use App\Models\Taxonomy;
use Bouncer;
use Hash;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $servers = Taxonomy::where('group', 'server')->where('type', 'type')->get();
        $limits = Taxonomy::where('group', 'server')->where('type', 'limit_type')->get();
        $services = Taxonomy::where('group', 'server')->where('type', 'service')->get();
        $statuses = Taxonomy::where('group', 'server')->where('type', 'status')->get();
        $aws = $services->where('code', 'aws')->first();

        $server = Server::create([
            'name' => 'Gestor Cliente - 1',
            'description' => 'Gestor Cliente 1',
            'ip' => '3.134.230.77',
            'dns' => 'ec2-3-134-230-77.us-east-2.compute.amazonaws.com',
            'identifier' => 'i-0949ed7a236ssecdc65',
            'password' => 'abcde',
            'token' => 'asdajabhskbmnbaqwkkqjkasdad',
            'active' => ACTIVE,
            'type_id' => $servers->where('code', 'instance')->first()->id,
            'service_id' => $aws->id,
            'status_id' => $statuses->where('code', 'full')->first()->id,
            'limit_type_id' => $limits->where('code', 'users')->first()->id,
            'max_limit' => 1000,
        ]);

        $server = Server::create([
            'name' => 'Gestor Cliente - 2',
            'description' => 'Gestor Cliente 2',
            'ip' => '3.17.185.94',
            'dns' => 'ec2-3-17-185-94.us-east-2.compute.amazonaws.com',
            'identifier' => 'i-094asda9ed7a236ssecdc65',
            'password' => 'abcdef',
            'token' => 'fasdajabhskbmnbaqwkkqjkasdad',
            'active' => ACTIVE,
            'type_id' => $servers->where('code', 'instance')->first()->id,
            'service_id' => $aws->id,
            'status_id' => $statuses->where('code', 'available')->first()->id,
            'limit_type_id' => $limits->where('code', 'users')->first()->id,
            'max_limit' => 10000,
        ]);

        $server = Server::create([
            'name' => 'Gestor Cliente - BCP',
            'description' => 'Gestor Cliente 3',
            'ip' => '32.19.55.34',
            'dns' => 'ec2-32-19-55-34.us-east-2.compute.amazonaws.com',
            'identifier' => 'i-3429ed7a236ssecdc65',
            'password' => 'abcde',
            'token' => 'asdajabhskbmnbaqwkkqjkasdad',
            'active' => ACTIVE,
            'type_id' => $servers->where('code', 'instance')->first()->id,
            'service_id' => $aws->id,
            'status_id' => $statuses->where('code', 'reserved')->first()->id,
            'limit_type_id' => $limits->where('code', 'users')->first()->id,
            'max_limit' => 15000,
        ]);

        $server = Server::create([
            'name' => 'RDS Small',
            'description' => 'RDS Small',
            'ip' => '54.23.72.34',
            'dns' => 'ec2-54-23-72-34.us-east-2.compute.amazonaws.com',
            'identifier' => 'i-123429ed7a236ssecdc65',
            'password' => 'abcde',
            'token' => 'asdajabhskbmnbaqwkkqjkasdad',
            'active' => ACTIVE,
            'type_id' => $servers->where('code', 'database')->first()->id,
            'service_id' => $aws->id,
        ]);

        $server = Server::create([
            'name' => 'RDS Medium',
            'description' => 'RDS Medium',
            'ip' => '154.3.12.36',
            'dns' => 'ec2-154-3-12-36.us-east-2.compute.amazonaws.com',
            'identifier' => 'i-123429ed7a236ssecdc65',
            'password' => 'abcde',
            'token' => 'asdajabhskbmnbaqwkkqjkasdad',
            'active' => ACTIVE,
            'type_id' => $servers->where('code', 'database')->first()->id,
            'service_id' => $aws->id,
        ]);

        $server = Server::create([
            'name' => 'S3 Small',
            'description' => 'S3 Small',
            'ip' => '11.5.72.127',
            'dns' => 'ec2-11-5-72-127.us-east-2.compute.amazonaws.com',
            'identifier' => 'i-as3429ed7a236ssecdc65',
            'password' => 'abcde',
            'token' => 'asdajabhskbmnbaqwkkqjkasdad',
            'active' => ACTIVE,
            'type_id' => $servers->where('code', 'storage')->first()->id,
            'service_id' => $aws->id,
        ]);

        $server = Server::create([
            'name' => 'Worker EC2 Small',
            'description' => 'Worker Small',
            'ip' => '3.5.34.231',
            'dns' => 'ec2-3-5-34-231.us-east-2.compute.amazonaws.com',
            'identifier' => 'i-as3429ed7a236ssecdc65',
            'password' => 'abcde',
            'token' => 'asdajabhskbmnbaqwkkqjkasdad',
            'active' => ACTIVE,
            'type_id' => $servers->where('code', 'worker')->first()->id,
            'service_id' => $aws->id,
        ]);

    }
}
