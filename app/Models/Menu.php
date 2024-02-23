<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    private $group_taxonomy = 'gestor';
    private $type_taxonomy = 'menu';
    private $subtype_taxonomy = 'menu';

    protected function list(){
        // return cache()->remember('list-menus', 1440,function () {
        //     return Taxonomy::select('id','group' ,'description','type' ,'position' ,'name','icon','extra_attributes')
        //         ->with(["children:id,parent_id,group,type,position,name,icon,extra_attributes"])
        //         ->where('group',$this->group_taxonomy)->where('type',$this->type_taxonomy)->orderBy('position','ASC')->get()->map(function($menu){
        //             $menu->is_beta = $menu->extra_attributes['is_beta'] ?? false;
        //             $menu->show_upgrade = $menu->extra_attributes['show_upgrade'] ?? false;
        //             foreach ($menu->children as $submenu) {
        //                 $submenu->is_beta = $submenu->extra_attributes['is_beta'] ?? false;
        //                 $submenu->show_upgrade = $submenu->extra_attributes['show_upgrade'] ?? false;
        //                 // unset($submenu->extra_attributes);
        //                 unset($submenu->children);
        //             }
        //             unset($menu->extra_attributes);
        //             return $menu;
        //         });
        //     });
        return Taxonomy::select('id','group' ,'description','type' ,'position' ,'name','icon','extra_attributes')
            ->with(["children:id,parent_id,group,type,position,name,icon,extra_attributes"])
            ->where('group',$this->group_taxonomy)->where('type',$this->type_taxonomy)->orderBy('position','ASC')->get()->map(function($menu){
                $menu->is_beta = $menu->extra_attributes['is_beta'] ?? false;
                $menu->show_upgrade = $menu->extra_attributes['show_upgrade'] ?? false;
                foreach ($menu->children as $submenu) {
                    $submenu->is_beta = $submenu->extra_attributes['is_beta'] ?? false;
                    $submenu->show_upgrade = $submenu->extra_attributes['show_upgrade'] ?? false;
                    // unset($submenu->extra_attributes);
                    unset($submenu->children);
                }
                unset($menu->extra_attributes);
                return $menu;
            });
    }
    protected function getMenuByUser($user,$platform){
        info($platform);
        if($platform && $platform == 'induccion') {
            $abilities_x_rol = $user->roles()->with('abilities')
                                            ->where('name', $platform)
                                            ->whereHas('abilities', function ($ability) {
                                                    $ability->where('name', 'show');
                                                })->first();
            $submenus_id = $abilities_x_rol ? $abilities_x_rol->abilities->pluck('entity_id')->toArray() : [];
        }
        else {
            $submenus_id = $user->getAbilities()->where('name','show')->pluck('entity_id')->toArray();
        }

        return Menu::list()->map(function($menu) use ($submenus_id){
            //Dar formato para front
            $items = [];
            $submenus = $menu->children->whereIn('id',$submenus_id);
            $show_upgrade = $menu->children->where('show_upgrade',true);
            $menu->children = $submenus->merge($show_upgrade)->unique('id');
            foreach ($menu->children as $submenu) {
                $show_upgrade = $submenu->show_upgrade && !in_array($submenu->id,$submenus_id);
                $items[]=[
                    'title' => $submenu->name,
                    'icon' => $submenu->icon,
                    'subpaths' => $submenu->extra_attributes['subpaths'],
                    'path' => !$show_upgrade ? $submenu->extra_attributes['path'] : '#',
                    'is_beta'=> $submenu->is_beta,
                    'show_upgrade'=> $show_upgrade,
                ];
            }
            if(count($menu->children)>0 || $menu->show_upgrade){
                // return $menu;
                $show_upgrade = $menu->show_upgrade && count($menu->children) == 0;
                return [
                    'title' => $menu->name,
                    'description' => $menu->description,
                    'icon' => $menu->icon,
                    'active' => false,
                    'is_beta'=> $menu->is_beta,
                    'show_upgrade'=> $show_upgrade,
                    'items' => $items
                ];
            }
        })->filter(function($menu){
            if($menu){
                return $menu;
            }
        })->values()->toArray();
    }
    protected function updateItems($menus){
        foreach ($menus as $index => $menu) {
            Taxonomy::updateOrCreate(
                ['id' => $menu['id']],
                [
                    'group' => $this->group_taxonomy,
                    'type' => $this->type_taxonomy,
                    'description' => $menu['description'],
                    'position' => $menu['position'],
                    'name' => $menu['name'],
                    'icon'=> $menu['icon'],
                    'extra_attributes'=>[
                        'is_beta'=> $menu['is_beta'] ?? false,
                        'show_upgrade'=> $menu['show_upgrade'] ?? false
                    ]
                ]
            );
            foreach ($menu['children'] as $children) {
                $submenu = Taxonomy::where('id',$children['id'])->first();
                $submenu->parent_id = $children['parent_id'];
                $submenu->position = $children['position'];
                $submenu->name = $children['name'];
                $submenu->icon = $children['icon'];
                $submenu->extra_attributes = [
                    'path'=> $submenu->extra_attributes['path'] ?? false,
                    'subpaths'=> $submenu->extra_attributes['subpaths'] ?? false,
                    'is_beta'=> $children['is_beta'] ?? false,
                    'show_upgrade'=> $children['show_upgrade'] ?? false
                ];
                $submenu->save();
            }
        }
        Cache::forget('list-menus'); //limpiar cache
        self::list(); //volver a guardar la consulta en cache
    }
}
