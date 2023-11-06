<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Ability;
use App\Models\Taxonomy;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class SetMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'set:menu';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'create menu or submenu';

    /**
     * Execute the console command.
     *
     * @return int
     */
    protected function configure()
    {
        $this->setName('set:menu')
            ->setDescription('create menu or submenu')
            ->addOption('name_menu', null, InputOption::VALUE_OPTIONAL, 'name taxonomy menu')
            ->addOption('icon_menu', null, null, 'icon taxonomy menu')
            ->addOption('name_submenu', null, InputOption::VALUE_OPTIONAL, 'name taxonomy submenu')
            ->addOption('code_submenu', null, InputOption::VALUE_OPTIONAL, 'code taxonomy submenu')
            ->addOption('icon_submenu', null, InputOption::VALUE_OPTIONAL, 'icon taxonomy submenu')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'path submenu')
            ->addOption('subpaths', null, InputOption::VALUE_OPTIONAL, 'subpaths submenu');
    }
    public function handle()
    {
        $this->setMenu();
    }

    private function setMenu(){
        $roles_to_sync = [];
        $parameters_required = [
            'name_menu',
            'name_submenu',
            'code_submenu',
            'icon_submenu',
            'path',
            'subpaths'
        ];
        $data['name_menu'] = $this->input->getOption('name_menu');
        $data['icon_menu'] = $this->input->getOption('icon_menu');
        $data['name_submenu'] = $this->input->getOption('name_submenu');
        $data['code_submenu'] = $this->input->getOption('code_submenu');
        $data['icon_submenu'] = $this->input->getOption('icon_submenu');
        $data['path'] = $this->input->getOption('path');
        $data['subpaths'] = explode(',',$this->input->getOption('subpaths'));
        foreach ($parameters_required as $parameter) {
            if(!isset($data[$parameter])){
                $this->info('Es necesario el parámetro '.$parameter);
                $this->info("php artisan set:menu --name_menu='USUARIO' --name_submenu='Invitados' --code_submenu='guests' --icon_submenu='fa fa-users' --path='invitados' --subpaths='invitados'");
                return;
            }
        }
        $menus = [
            [
                'group'=>'gestor',
                'type' => 'menu',
                'position'=>1,
                'name' =>  $data['name_menu'],
                'icon' => $data['icon_menu'],
                'children' => [
                    [
                        'group'=>'gestor',
                        'type' => 'submenu',
                        'code' => $data['code_submenu'],
                        'name' => $data['name_submenu'],
                        'icon' =>  $data['icon_submenu'],
                        'extra_attributes'=>[
                            'path'=> "/".$data['path'],
                            'subpaths' => $data['subpaths'],
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
                $this->info('Se creó el menú '.$menu['name']);
            }else{
                $this->info('El menú ya estaba creado '.$menu['name']);
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
                    $this->info('Se creó el submenú '.$children['name']);
                }else{
                    $this->info('El submenú ya estaba creando '.$children['name']);
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
                $this->info('Se crearon las habilidades');
                foreach ($children['roles'] as  $role_name) {
                    if(isset($roles_to_sync[$role_name])){
                        $roles_to_sync[$role_name] = array_merge($roles_to_sync[$role_name],$abilities_id);
                    }else{
                        $roles_to_sync[$role_name] = $abilities_id;
                    }
                }
                $this->info('Se asigno el submenú al superusuario');
            }

        }
        foreach ($roles_to_sync as $role_name => $abilities_id) {
            $role = Role::where('name',$role_name)->first();
            $role->abilities()->syncWithoutDetaching($abilities_id);
        }
    }
}
