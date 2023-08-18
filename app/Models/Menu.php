<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    private $group_taxonomy = 'gestor';
    private $type_taxonomy = 'menu';
    private $subtype_taxonomy = 'menu';
    
    protected function list(){
        return Taxonomy::select('id','group' , 'type' ,'position' ,'name','icon','extra_attributes')
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
    protected function getMenuByUser($user){
        $submenus_id = $user->getAbilities()->where('name','show')->pluck('entity_id');
        return Menu::list()->filter(function($menu) use ($submenus_id){
            $menu->children = $menu->children->whereIn('id',$submenus_id);
            if(count($menu->children)>0 || $menu->show_upgrade){
                return $menu;
            }
        })->map(function($menu){
            $items = [];
            foreach ($menu->children as $submenu) {
                $items[]=[
                    'title' => $submenu->name,
                    'icon' => $submenu->icon,
                    'subpaths' => $submenu->extra_attributes['subpaths'],
                    'path' => $submenu->extra_attributes['path'],
                    'isBeta'=> $submenu->is_beta,
                    'showUpgrade'=> $submenu->show_upgrade,
                    'selected'=>false
                ];
            } 
            return [
                'title' => $menu->name,
                'icon' => $menu->icon,
                'active' => false,
                'is_beta'=> $menu->is_beta,
                'show_upgrade'=> $menu->show_upgrade,
                'items' => $items
            ];
        })->toArray();
    }
    protected function updateItems($menus){
        foreach ($menus as $index => $menu) {
            Taxonomy::updateOrCreate(
                ['id' => $menu['id']],
                [
                    'group' => $this->group_taxonomy,
                    'type' => $this->type_taxonomy,
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
    }
}
