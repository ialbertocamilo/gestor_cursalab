<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Ability;
use App\Models\Taxonomy;
use Illuminate\Console\Command;

class SetMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:menu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setMenu();
    }

    private function setMenu(){
        $roles_to_sync = [];
        $menus = [
            [
                'group'=>'gestor',
                'type' => 'menu',
                'position'=>1,
                'name' => "RESUMEN",
                'icon' => "fas fa-dice-d6",
                'children' => [
                    [
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'code' => 'projects',
                        'name' => "Tareas",
                        'icon' => "fa fa-home",
                        'extra_attributes'=>[
                            'path'=> "/projects",
                            'subpaths' => ["projects"],
                        ],
                        'abilities' =>[
                            [
                            'name' => 'show',
                            'title' => 'Mostrar submenú',
                            ]
                        ],
                        'roles' => [
                            "super-user",
                        ]
                    ]
                ]
            ]
        ];
        foreach ($menus as $index_menu => $menu) {
            $new_menu = Taxonomy::where('group','gestor')->where('type','menu')->where('name',$menu['name'])->first();
            if(!$new_menu){
                $new_menu = new Taxonomy();
                $new_menu->group = 'gestor';
                $new_menu->type = 'menu';
                $new_menu->position = $index_menu+1;
                $new_menu->name = $menu['name'];
                $new_menu->icon = $menu['icon'];
                $new_menu->extra_attributes = [
                    'is_beta'=> $menu['is_beta'] ?? false,
                    'show_upgrade'=> $menu['show_upgrade'] ?? false,
                ];
                $new_menu->save();
            }
            
            foreach ($menu['children'] as $index_submenu => $children) {
                $submenu = Taxonomy::where('group','gestor')->where('type','submenu')->where('name',$children['name'])->first();
                if(!$submenu){
                    $submenu = new Taxonomy();
                    $submenu->group = 'gestor';
                    $submenu->type = 'submenu';
                    $submenu->parent_id = $new_menu->id;
                    $submenu->position = $index_submenu+1;
                    $submenu->code = $children['code'];
                    $submenu->name = $children['name'];
                    $submenu->icon = $children['icon'];
                    $submenu->extra_attributes = $children['extra_attributes'];
                    $submenu->save();
                }
                $abilities_id = [];
                foreach ($children['abilities'] as $children_ability) {
                    $ability = Ability::where('title',$children_ability['title'])->where('name',$children_ability['name'])->where('entity_id',$submenu->id)->first();
                    if(!$ability){
                        $ability = new Ability();
                        $ability->title = $children_ability['title'];
                        $ability->name = $children_ability['name'];
                        $ability->entity_id = $submenu->id;
                        $ability->save();
                    }
                    $abilities_id[] = $ability->id;
                }
                foreach ($children['roles'] as  $role_name) {
                    if(isset($roles_to_sync[$role_name])){
                        $roles_to_sync[$role_name] = array_merge($roles_to_sync[$role_name],$abilities_id);
                    }else{
                        $roles_to_sync[$role_name] = $abilities_id;
                    }
                }
            }
        }
        foreach ($roles_to_sync as $role_name => $abilities_id) {
            $role = Role::where('name',$role_name)->first();
            $role->abilities()->syncWithoutDetaching($abilities_id);
        }
    }
}
