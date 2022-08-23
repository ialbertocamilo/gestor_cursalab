<?php

namespace Database\Seeders;

use App\Models\AssignedRole;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    // Workspaces list

    public static $new_admins = [
    
        [
            'name'=> 'Maria Ego Aguirre',
            'email'=> 'Maria.EgoAguirre@intercorpretail.pe',
            'workspaces'=>
            [
                [
                    'id'=>1,
                    'role_id'=>3,
                ],
                [
                    'id'=>5,
                    'role_id'=>3,
                ],
                [
                    'id'=>8,
                    'role_id'=>3,
                ],
                [
                    'id'=>11,
                    'role_id'=>3,
                ],
                [
                    'id'=>14,
                    'role_id'=>3,
                ],
                [
                    'id'=>16,
                    'role_id'=>3,
                ],
                [
                    'id'=>18,
                    'role_id'=>3,
                ],
                [
                    'id'=>25,
                    'role_id'=>3,
                ],
            ]
        ],
        
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->insertAdmins();
        $this->insertNewAdmins();
        //$this->insertUsers();
    }

    /**
     * Insert workspaces and subworkspaces
     */
    public function insertNewAdmins() {

        foreach(static::$new_admins AS $child)
        {
            // Create admins
            $user = User::create([
                'email'=>$child['email'],
                'name' => $child['name'],
                'password' => 'CursalabV2-$2022',
                'active' => 1
            ]);

            // Assign admins
            foreach ($child['workspaces'] as $key => $wk_arr)
            {
                AssignedRole::insert([
                    'entity_type' => AssignedRole::USER_ENTITY,
                    'entity_id' => $user->id,
                    'scope' => $wk_arr['id'],
                    'role_id' => $wk_arr['role_id']
                ]);
            }

        }         
    }
}
