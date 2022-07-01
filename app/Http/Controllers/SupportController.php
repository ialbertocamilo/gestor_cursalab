<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use File;

class SupportController extends Controller
{
    public function check_noused_files(){
        
        // DB::enableQueryLog();
       
        // IMAGES
        $files = File::files( public_path('images') );
        // dd($files);
        $cant = 0;
        $total_size = 0;
        echo "<h3>IMAGENES (No existen en BD)</h3>";
        echo "<ul>";
        foreach($files as $file){
            $filename = $file->getFileName();
            // Modulos
            $res = DB::table('ab_config')->select('id')->where('logo', 'like', "%{$filename}%")->orWhere('isotipo', 'like', "%{$filename}%")->orWhere('plantilla_diploma', 'like', "%{$filename}%")->first();
            if($res){
                continue;
            } 
            // Categorias
            $res = DB::table('categorias')->select('id')->where('imagen', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            // cursos
            $res = DB::table('cursos')->select('id')->where('imagen', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            // posteos
            $res = DB::table('posteos')->select('id')->where('imagen', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            // encuestas
            $res = DB::table('encuestas')->select('id')->where('imagen', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            // carreras
            $res = DB::table('carreras')->select('id')->where('imagen', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            // ciclos
            $res = DB::table('ciclos')->select('id')->where('imagen', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            // anuncios
            $res = DB::table('anuncios')->select('id')->where('imagen', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            // ayudas
            $res = DB::table('ayudas')->select('id')->where('imagen', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            else{
                $cant ++;
                $size = $file->getSize();
                $total_size += $size;
                // Nombrar
                echo "<li>{$filename} ({$size})</li>";
                // Eliminar
                // File::delete($file->getPathName());
                // echo "<li>{$filename} ({$size}) - eliminado</li>";

            }
        }
        echo "</ul>";
        echo "<p>Total archivos a eliminar: {$cant}</p>";
        $peso_megas = (((int) $total_size/1024)/1024);
        $peso_gigas = (((int) $total_size/1024)/1024)/1024;
        echo "<p>Total peso: {$peso_megas} MB</p>";

        // ARCHIVOS (VIDEOS/PDF)
        $files = File::files( public_path('archivos') );
        $cant = 0;
        echo "<h3>ARCHIVOS/VIDEOS/PDF (No existen en BD)</h3>";
        echo "<ul>";
        foreach($files as $file){
            $filename = $file->getFileName();
            // posteos
            $res = DB::table('posteos')->select('id')->where('archivo', 'like', "%{$filename}%")->orWhere('video', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            // carreras
            $res = DB::table('carreras')->select('id')->where('malla_archivo', 'like', "%{$filename}%")->first();
            if ($res){
                continue;
            }
            else{
                $cant ++;
                $size = $file->getSize();
                $total_size += $size;
                // Nombrar
                echo "<li>{$filename} ({$size})</li>";
                // Eliminar
                // File::delete($file->getPathName());
                // echo "<li>{$filename} ({$size}) - eliminado</li>";
            }
        }
        echo "</ul>";
        echo "<p>Total archivos a eliminar: {$cant}</p>";
        $peso_megas = (((int) $total_size/1024)/1024);
        $peso_gigas = (((int) $total_size/1024)/1024)/1024;
        echo "<p>Total peso: {$peso_megas} MB</p>";
    }

    // dd(DB::getQueryLog());
}
