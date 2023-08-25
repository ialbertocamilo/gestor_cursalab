<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiplomaSearchResource;
use App\Models\Media;
use App\Models\{ Certificate as Diploma };
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class DiplomaController extends Controller
{
    private $dias_ES = array("lunes", "martes", "miércoles", "jueves", "viernes", "sábado", "domingo");
    private $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    private $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    private $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

    public function __construct()
    {
        if (!\Session::has('config')) {
            $data = DB::table('config_general')->first();
            session(['config' => $data]);
        }
    }
    
    public function show(){
        return view('diplomas.index');
    }

    public function search(Request $request)
    {
        $diplomas = Diploma::search($request);
        DiplomaSearchResource::collection($diplomas);

        return $this->success($diplomas);
    }

    public function getMediaStreamBase64($media_id)
    {
        $media = Media::find($media_id);
        $image = Storage::disk('s3')->get($media->file);
        return "data:image/png;base64,".base64_encode($image);
    }

    public function searchDiploma(Diploma $diploma)
    {
        // === fondo de plantilla ===
        $info_bg_decoded = json_decode($diploma->info_bg);
        $info_bg_decoded->src = $this->getMediaStreamBase64($info_bg_decoded->media_id);

        // === partes de plantilla ===
        $s_objects_decoded = json_decode($diploma->s_objects);
        $s_objects_decoded = collect($s_objects_decoded);

        $array_medias = $s_objects_decoded->where('type', 'image');
        $array_text = $s_objects_decoded->where('type', 'i-text');

        foreach ($array_medias as $media_row ) {
            // === media_id ===
            $media_row->src = $this->getMediaStreamBase64($media_row->media_id);
        }

        $diploma['s_object_bg'] = $info_bg_decoded;
        $diploma['s_objects_images'] = $array_medias;
        $diploma['s_objects_text'] = $array_text;

        // === imagen plantilla completa ===
        // info(['diploma' => $diploma->toArray() ]);

        $plantilla = Storage::disk('s3')->get($diploma->path_image);
        $plantilla = "data:image/png;base64," . base64_encode($plantilla);
        
        return response()->json(compact('diploma', 'plantilla'));
    }

    public function update(Diploma $diploma, Request $request)
    {
        // === images a bases 64 ====
        $images_bases64 = Diploma::getBasesFromImages($request->get('edit_plantilla'));

        $data = $request->get('info'); // del canvas
        $c_data = collect($data['objects']);
        $bg = $data['backgroundImage'];

        $e_statics = $c_data->where('static',true);
        $e_dinamics = $c_data->where('static',false);

        if($bg) {
            $im = $this->image_create($bg['src'],1,1,1,1);
            $x = $bg['left'];
            $y = $bg['top'];

            $nombre_plantilla = 'plantilla_'.$request->get('nombre_plantilla');
            $info_s_objects = []; // s_objects[]

            foreach ($e_statics as $e_static) {
                // $font = realpath('.').'/fonts/tahoma.ttf';
                $e_static_type = $e_static['type'];

                switch ($e_static_type) {
                    case 'i-text':
                        //  === para el font ===
                        $fontName = 'calisto-mt.ttf';
                        if ($e_static['fontStyle'] === 'italic' && $e_static['fontWeight'] === 'bold') {
                            $fontName = 'calisto-mt-bold-italic.ttf';
                        }else if($e_static['fontStyle'] === 'italic') {
                            $fontName = 'calisto-mt-italic.ttf';
                        }else if($e_static['fontWeight'] === 'bold') {
                            $fontName = 'calisto-mt-bold.ttf';
                        }

                        $font = realpath('.').'/fonts/diplomas/'.$fontName;
                        //  === para el font ===

                        $rgb = $this->hex2rgb($e_static['fill']);
                        $color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
                        $lines = preg_split('/\n|\r/',$e_static['text']);

                        // resize font;
                        $divideSize = $e_static['fontSize'] / 3.3;
                        $fontsize = $e_static['fontSize'] - $divideSize;

                        imagettftext(
                            $im,
                            $fontsize,
                            0,
                            $e_static['left']-$x,
                            $e_static['top']-$y,
                            $color,
                            $font,
                            utf8_decode($e_static['text'])
                        );
                        
                        $info_s_objects[] = Diploma::pushType_iText($e_static);
                    break;
                    case 'image':
                        $im2 = $this->image_create($e_static['src'],$e_static['scaleX'],$e_static['scaleY'],$e_static['width'],$e_static['height']);
                        
                        $this->imagecopymerge_alpha(
                            $im, // destino base
                            $im2, // fuente base
                            $e_static['left']-$x,
                            $e_static['top']-$y,
                            0,
                            0,
                            $e_static['width']*$e_static['scaleX'],
                            $e_static['height']*$e_static['scaleY'],
                            100);
                        
                        // === guarda imagen - media y retorna id ===
                        $info_s_objects[] = Diploma::pushType_image($e_static, $nombre_plantilla, true, $images_bases64);
                    break;
                }
            }

            $info_d_objects = []; // d_objects[]

            foreach ($e_dinamics as $e_dinamic) {
                if($e_dinamic['type']=='text'){
                    $info_d_objects [] = [
                        'type'=>$e_dinamic['type'],
                        'fill'=>$e_dinamic['fill'],
                        'text'=>$e_dinamic['text'],
                        'id'=>$e_dinamic['id'],
                        'id_formato'=>$e_dinamic['id_formato'],
                        'left'=>$e_dinamic['left'],
                        // 'left_calc'=>$e_dinamic['left'] - $x,
                        'centrado'=>$e_dinamic['centrado'],
                        'fontSize'=>$e_dinamic['fontSize'],
                        'top'=>$e_dinamic['top'],
                        // 'top_calc'=>$e_dinamic['top'] - $y,
                        'height'=>$e_dinamic['height'],
                        'width'=>$e_dinamic['width'],
                        'fontWeight'=>$e_dinamic['fontWeight'],
                        'fontStyle' => $e_dinamic['fontStyle'],
                        'zoomX'=> $e_dinamic['zoomX'] ?? null,
                    ];
                }
            }

            // === guarda imagen - media y retorna id ===
            $info_bg = Diploma::pushType_image($bg, $nombre_plantilla, true, $images_bases64);

            $nombre_plantilla_final = Media::generateNameFile($nombre_plantilla, 'jpg');
            $path = 'images/diplomas/'.$nombre_plantilla_final;
            $preview = $this->jpg_to_base64($im);

            // info(['preview' => $preview]);

            // === guarda imagen - media ===
            $media = Diploma::uploadMediaBase64($nombre_plantilla_final, $path, $preview);

            // === guarda diploma ===
            $diploma->media_id = $media->id;
            $diploma->title = $request->get('nombre_plantilla');
            $diploma->path_image = $path;
            $diploma->info_bg = json_encode($info_bg);
            $diploma->s_objects = json_encode($info_s_objects);
            $diploma->d_objects = json_encode($info_d_objects);

            $diploma->save();

            return response()->json(['error' => false]);
        }
        return response()->json(['error' => true]);
    }
    
    public function save(Request $request){
        $data = $request->get('info'); // del canvas

        $c_data = collect($data['objects']);
        $bg = $data['backgroundImage'];

        $e_statics = $c_data->where('static',true);
        $e_dinamics = $c_data->where('static',false);

        if($bg){
            $im = $this->image_create($bg['src'],1,1,1,1);
            $x = $bg['left'];
            $y = $bg['top'];

            $nombre_plantilla = 'plantilla_'.$request->get('nombre_plantilla');
            $info_s_objects = []; // s_objects[]

            foreach ($e_statics as $e_static) {
                // $font = realpath('.').'/fonts/tahoma.ttf';
                $e_static_type = $e_static['type'];

                switch ($e_static_type) {
                    case 'i-text':

                        //  === para el font ===
                        $fontName = 'calisto-mt.ttf';
                        if ($e_static['fontStyle'] === 'italic' && $e_static['fontWeight'] === 'bold') {
                            $fontName = 'calisto-mt-bold-italic.ttf';
                        }else if($e_static['fontStyle'] === 'italic') {
                            $fontName = 'calisto-mt-italic.ttf';
                        }else if($e_static['fontWeight'] === 'bold') {
                            $fontName = 'calisto-mt-bold.ttf';
                        }

                        $font = realpath('.').'/fonts/diplomas/'.$fontName;
                        //  === para el font ===

                        $rgb = $this->hex2rgb($e_static['fill']);
                        $color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
                        $lines = preg_split('/\n|\r/',$e_static['text']);

                        // resize font;
                        $divideSize = $e_static['fontSize'] / 3.3;
                        $fontsize = $e_static['fontSize'] - $divideSize;

                        imagettftext(
                            $im,
                            $fontsize,
                            0,
                            $e_static['left']-$x,
                            $e_static['top']-$y,
                            $color,
                            $font,
                            utf8_decode($e_static['text'])
                        );

                        $info_s_objects[] = Diploma::pushType_iText($e_static);
                    break;
                    case 'image':
                        $im2 = $this->image_create($e_static['src'],$e_static['scaleX'],$e_static['scaleY'],$e_static['width'],$e_static['height']);
                        
                        $this->imagecopymerge_alpha(
                            $im, // destino base
                            $im2, // fuente base
                            $e_static['left']-$x,
                            $e_static['top']-$y,
                            0,
                            0,
                            $e_static['width']*$e_static['scaleX'],
                            $e_static['height']*$e_static['scaleY'],
                            100);
                        
                        // === guarda imagen - media y retorna id ===
                        $info_s_objects[] = Diploma::pushType_image($e_static, $nombre_plantilla);
                    break;
                }
            }

            $info_d_objects = []; // d_objects[]

            foreach ($e_dinamics as $e_dinamic) {
                if($e_dinamic['type']=='text'){
                    $info_d_objects [] = [
                        'type'=>$e_dinamic['type'],
                        'fill'=>$e_dinamic['fill'],
                        'text'=>$e_dinamic['text'],
                        'id'=>$e_dinamic['id'],
                        'id_formato'=>$e_dinamic['id_formato'],
                        'left'=>$e_dinamic['left'],
                        // 'left_calc'=>$e_dinamic['left'] - $x,
                        'centrado'=>$e_dinamic['centrado'],
                        'fontSize'=>$e_dinamic['fontSize'],
                        'top'=>$e_dinamic['top'],
                        // 'top_calc'=>$e_dinamic['top'] - $y,
                        'height'=>$e_dinamic['height'],
                        'width'=>$e_dinamic['width'],
                        'fontWeight'=>$e_dinamic['fontWeight'],
                        'fontStyle' => $e_dinamic['fontStyle'],
                        'zoomX'=> $e_dinamic['zoomX'] ?? null,
                    ];
                }
            }

            // === guarda imagen - media y retorna id ===
            $info_bg = Diploma::pushType_image($bg, $nombre_plantilla);

            $nombre_plantilla_final = $nombre_plantilla.'_'.rand(0,100).'.jpg';
            $path = 'images/'.$nombre_plantilla_final;
            $preview = $this->jpg_to_base64($im);

            // info(['preview' => $preview]);

            // === guarda imagen - media ===
            $media = Diploma::uploadMediaBase64($nombre_plantilla_final, $path, $preview);

            // === guarda diploma ===
            $diploma = new Diploma;
            $diploma->media_id = $media->id;
            $diploma->title = $request->get('nombre_plantilla');
            $diploma->path_image = $path;
            $diploma->info_bg = json_encode($info_bg);
            $diploma->s_objects = json_encode($info_s_objects);
            $diploma->d_objects = json_encode($info_d_objects);

            $diploma->save();

            return response()->json(['error' => false]);
        }
        return response()->json(['error' => true]);
    }

    public function get_diploma($pathImage, $d_per, $info, $real_info) {

        $e_dinamics = $d_per;
        $bg_info = $info;

        $x = $bg_info['left'];
        $y = $bg_info['top'];
        $width = $bg_info['width'];

        $image = file_get_contents(get_media_url($pathImage));

        // $image = imagecreatefromstring($image);
        foreach ($e_dinamics as $e_dinamic) {
            $font = realpath('.').'/fonts/diplomas/calisto-mt.ttf';
            if($e_dinamic['type']=='text'){
                $rgb = $this->hex2rgb($e_dinamic['fill']);
                $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
                $text = $this->get_text($e_dinamic, $real_info);
                $text = wordwrap($text, 35, "multiline");
                
                $fontsize =  $e_dinamic['fontSize'];

                if (isset($e_dinamic['zoomX']) && $e_dinamic['zoomX']) {
                    $fontsize =  $fontsize - ($fontsize * $e_dinamic['zoomX']);
                }

                $left = $e_dinamic['left']-$x;
                $top = $e_dinamic['top']-$y+$fontsize;

                ($e_dinamic['fontStyle']=='italic' && $e_dinamic['fontWeight']!='bold') && $font = realpath('.').'/fonts/diplomas/calisto-mt-italic.ttf';
                ($e_dinamic['fontStyle']!='italic' && $e_dinamic['fontWeight']=='bold') && $font = realpath('.').'/fonts/diplomas/calisto-mt-bold.ttf';
                ($e_dinamic['fontStyle']=='italic' && $e_dinamic['fontWeight']=='bold') && $font = realpath('.').'/fonts/diplomas/calisto-mt-bold-italic.ttf';

                //Eliminar emogis
                $text = trim($this->remove_emoji($text));
                //Centrado multilinea
                $explode_text = explode('multiline',$text);

                foreach ($explode_text as $e_text) {
                    if(isset($e_dinamic['centrado']) && ($e_dinamic['centrado']=="true")){
                        $calculateTextBox = $this->calculateTextBox($fontsize, 0, $font, trim($e_text));
                        $left = ($bg_info['width']/2) - (($calculateTextBox['width']/2));
                        // info($bg['width'].'-'.$calculateTextBox['width'].'-'.$left.'-'.$text.' .');
                    }

                    imagettftext($image,$fontsize,0 ,$left,$top , $color, $font,utf8_decode(trim($e_text)));
                    $top = $top + $fontsize+(0.2*$fontsize);
                }
            }
        }
        
        //Añadir marca de agua al 10% de la imagen total
        // $ambiente = DB::table('config_general')->select('marca_agua')->first();
        // if(!is_null($ambiente->marca_agua)){
        //     $marca_agua = json_decode($ambiente->marca_agua);
        //     if($marca_agua->estado){
        //         $watermark = imagecreatefrompng($marca_agua->url);
        //         $ancho = imagesx($image)*0.12;
        //         $alto = round($ancho  * imagesy($watermark) / imagesx($watermark) );
        //         $watermark = $this->getImageResized($watermark,$ancho,$alto);
        //         imagecopymerge($image, $watermark,$bg_info['width'] - $ancho, $bg_info['height']-$alto, 0, 0,imagesx($watermark), imagesy($watermark), 40);
        //     }
        // }
        $preview = $this->jpg_to_base64($image);
        return $preview;
    }
    function remove_emoji($text) {
        $clean_text = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
    
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
    
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
    
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
    
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        $clean_text = preg_replace('/[^ -\x{2122}]\s+|\s*[^ -\x{2122}]/u','',$clean_text);
        return $clean_text;
    }

    public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $source_img = imagecreatetruecolor($src_w, $src_h);// crear caja negra "width x height"

        imagesavealpha($source_img, true); // establecer canal alfa
        $transparency = imagecolorallocatealpha($source_img , 0, 0, 0, 127); // asginamos el color a la imagen
        imagefill($source_img , 0, 0, $transparency); // pintamos nuestra caja en alpha

        // redimensionamos la imagen  de origen $src_im con $src_w, $src_h
        imagecopyresampled($source_img, $src_im, 0, 0, 0, 0, $src_w, $src_h, imagesx($src_im), imagesy($src_im));

        // Crear un recurso de imagen de corte
        $copy = imagecreatetruecolor($src_w, $src_h);
    
        imagecopy($copy, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        imagecopy($copy, $source_img, 0, 0, $src_x, $src_y, $src_w, $src_h);

        // Fusionar las imágenes con opacidad
        imagecopymerge($dst_im, $copy, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
    }

    public function get_preview_data(Request $request) {

        $data = $request->get('info');
        $zoom = $request->get('zoom');
        $response = $request->get('response');
        $user_data = $request->get('user_data');

        $c_data = collect($data['objects']);
        $bg = $data['backgroundImage'];
        
        $e_statics = $c_data->where('static',true);
        $e_dinamics = $c_data->where('static',false);

        if($bg){
            $im = $this->image_create($bg['src'],1,1,1,1);
            $x = $bg['left'];
            $y = $bg['top'];

            foreach ($e_statics as $e_static) {
                switch ($e_static['type']) {
                    case 'i-text':

                        //  === para el font ===
                        $fontName = 'calisto-mt.ttf';
                        if ($e_static['fontStyle'] === 'italic' && $e_static['fontWeight'] === 'bold') {
                            $fontName = 'calisto-mt-bold-italic.ttf';
                        }else if($e_static['fontStyle'] === 'italic') {
                            $fontName = 'calisto-mt-italic.ttf';
                        }else if($e_static['fontWeight'] === 'bold') {
                            $fontName = 'calisto-mt-bold.ttf';
                        }

                        $font = realpath('.').'/fonts/diplomas/'.$fontName;
                        //  === para el font ===

                        $rgb = $this->convertHexadecimalToRGB($e_static['fill']);
                        $color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);

                        // resize font;
                        $divideSize = $e_static['fontSize'] / 3.3;
                        $fontsize = $e_static['fontSize'] - $divideSize;

                        imagettftext(
                            $im,
                            $fontsize,
                            0,
                            $e_static['left']-$x,
                            $e_static['top']-$y,
                            $color,
                            $font,
                            utf8_decode($e_static['text'])
                        );

                    break;
                    case 'image':

                          
                        $im2 = $this->image_create($e_static['src'],$e_static['scaleX'],$e_static['scaleY'],$e_static['width'],$e_static['height']);
                        

                        $this->imagecopymerge_alpha(
                            $im, // destino base
                            $im2, // fuente base
                            $e_static['left']-$x,
                            $e_static['top']-$y,
                            0,
                            0,
                            $e_static['width']*$e_static['scaleX'],
                            $e_static['height']*$e_static['scaleY'],
                            100);

                    break;
                }
            }
            foreach ($e_dinamics as $e_dinamic) {
                $font = realpath('.').'/fonts/diplomas/calisto-mt.ttf';
                if($e_dinamic['type']=='text'){
                    $rgb = $this->convertHexadecimalToRGB($e_dinamic['fill']);
                    $color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);

                    $text = $this->get_text($e_dinamic, $user_data);
                    $text = wordwrap($text, 35, "multiline");

                    $fontsize =  $e_dinamic['fontSize'];

                    if (isset($e_dinamic['zoomX']) && $e_dinamic['zoomX']) {
                        $fontsize =  $fontsize - ($fontsize * $e_dinamic['zoomX']);
                    }

                    $left = $e_dinamic['left']-$x;
                    $top = $e_dinamic['top']-$y+$fontsize; // v1

                    ($e_dinamic['fontStyle']=='italic' && $e_dinamic['fontWeight']!='bold') && $font = realpath('.').'/fonts/diplomas/calisto-mt-italic.ttf';
                    ($e_dinamic['fontStyle']!='italic' && $e_dinamic['fontWeight']=='bold') && $font = realpath('.').'/fonts/diplomas/calisto-mt-bold.ttf';
                    ($e_dinamic['fontStyle']=='italic' && $e_dinamic['fontWeight']=='bold') && $font = realpath('.').'/fonts/diplomas/calisto-mt-bold-italic.ttf';

                    //Centrado multilinea
                    $explode_text = explode('multiline',$text);
                    foreach ($explode_text as $e_text) {
                        if(isset($e_dinamic['centrado']) && ($e_dinamic['centrado']=="true")){
                            $calculateTextBox = $this->calculateTextBox($fontsize, 0, $font, trim($e_text));
                            $left = ($bg['width']/2) - (($calculateTextBox['width']/2));
                            // info($bg['width'].'-'.$calculateTextBox['width'].'-'.$left.'-'.$text.' .');
                        }

                        imagettftext($im,$fontsize,0 ,$left,$top , $color, $font, utf8_decode($e_text));
                        $top = $top + $fontsize+(0.2*$fontsize);

                    }
                }
            }
            // //Añadir marca de agua al 10% de la imagen total
            // $ambiente = DB::table('config_general')->select('marca_agua')->first();
            // if(!is_null($ambiente->marca_agua)){
            //     $marca_agua = json_decode($ambiente->marca_agua);
            //     if($marca_agua->estado){
            //         $watermark = imagecreatefrompng($marca_agua->url);
            //         $ancho = imagesx($im)*0.12;
            //         $alto = round($ancho  * imagesy($watermark) / imagesx($watermark) );
            //         $watermark = $this->getImageResized($watermark,$ancho,$alto);
            //         imagecopymerge($im, $watermark,$bg['width'] - $ancho, $bg['height']-$alto, 0, 0,imagesx($watermark), imagesy($watermark), 40);
            //     }
            // }
        }

        $preview = $this->jpg_to_base64($im);

        if ($response == 'only-data') {
            return $preview;
        }

        return response()->json(compact('preview'));
    }
    private function calculateTextBox($font_size, $font_angle, $font_file, $text) {
        $box   = imagettfbbox($font_size, $font_angle, $font_file, $text);
        if( !$box )
          return false;
        $min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
        $max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
        $min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
        $max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
        $width  = ( $max_x - $min_x );
        $height = ( $max_y - $min_y );
        $left   = abs( $min_x ) + $width;
        $top    = abs( $min_y ) + $height;
        // to calculate the exact bounding box i write the text in a large image
        $img     = @imagecreatetruecolor( $width << 2, $height << 2 );
        $white   =  imagecolorallocate( $img, 255, 255, 255 );
        $black   =  imagecolorallocate( $img, 0, 0, 0 );
        imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);
        // for sure the text is completely in the image!
        imagettftext( $img, $font_size,
                      $font_angle, $left, $top,
                      $white, $font_file, $text);
        // start scanning (0=> black => empty)
        $rleft  = $w4 = $width<<2;
        $rright = 0;
        $rbottom   = 0;
        $rtop = $h4 = $height<<2;
        for( $x = 0; $x < $w4; $x++ )
          for( $y = 0; $y < $h4; $y++ )
            if( imagecolorat( $img, $x, $y ) ){
              $rleft   = min( $rleft, $x );
              $rright  = max( $rright, $x );
              $rtop    = min( $rtop, $y );
              $rbottom = max( $rbottom, $y );
            }
        // destroy img and serve the result
        imagedestroy( $img );
        return array( "left"   => $left - $rleft,
                      "top"    => $top  - $rtop,
                      "width"  => $rright - $rleft + 1,
                      "height" => $rbottom - $rtop + 1 );
      }
    public function getImageResized($image, int $newWidth, int $newHeight) {
        $newImg = imagecreatetruecolor($newWidth, $newHeight);
        // imagealphablending($newImg, false);
        // imagesavealpha($newImg, true);
        // $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        // imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
        $src_w = imagesx($image);
        $src_h = imagesy($image);
        imagecopyresampled($newImg, $image, 0, 0, 0, 0, $newWidth, $newHeight, $src_w, $src_h);
        return $newImg;
    }
    private function convertHexadecimalToRGB($hex) {
        $hex = str_replace("#", "", $hex);
        if(strlen($hex) == 3) {
          $r = hexdec(substr($hex,0,1).substr($hex,0,1));
          $g = hexdec(substr($hex,1,1).substr($hex,1,1));
          $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
          $r = hexdec(substr($hex,0,2));
          $g = hexdec(substr($hex,2,2));
          $b = hexdec(substr($hex,4,2));
        }
        return array($r, $g, $b); // RETURN ARRAY INSTEAD OF STRING
    }
    private function image_create($data,$scaleX,$scaleY,$width,$height){
        $exploded = explode(',', $data, 2); // limit to 2 parts, i.e: find the first comma
        $encoded = $exploded[1]; // pick up the 2nd part
        $decoded = base64_decode($encoded);
        $img_handler = imagecreatefromstring($decoded);
        if ($scaleX >1 && $scaleY>1) {
            $newwidth = $width * $scaleX;
            $newheight = $height * $scaleY;
            $img_handler=imagescale($img_handler,$newwidth,$newheight,IMG_BILINEAR_FIXED);
        }
        return $img_handler;
    }
    private function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if(strlen($hex) == 3) {
          $r = hexdec(substr($hex,0,1).substr($hex,0,1));
          $g = hexdec(substr($hex,1,1).substr($hex,1,1));
          $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
          $r = hexdec(substr($hex,0,2));
          $g = hexdec(substr($hex,2,2));
          $b = hexdec(substr($hex,4,2));
        }
        return array($r, $g, $b); // RETURN ARRAY INSTEAD OF STRING
    }

    private function jpg_to_base64($data) {
        ob_start(); // Let's start output buffering.
            imagejpeg($data); //This will normally output the image, but because of ob_start(), it won't.
            $contents = ob_get_contents(); //Instead, output above is saved to $contents
        ob_end_clean(); //End the output buffer.
        $dataUri = "data:image/jpeg;base64," . base64_encode($contents);
        return $dataUri;
    }

    public function get_text($e_dinamic, $real_data = [])
    {
        $text = $e_dinamic['text'];
        $upper_string = true;

        switch ($e_dinamic['id']) {
            case 'users':
                $text = $real_data['users'] ?? 'Usuario Prueba Cursalab';
            break;
            case 'course-average-grade':
                $text = $real_data['course-average-grade'] ?? 18;
                break;
            case 'courses':
                $text = $real_data['courses'] ?? 'Curso de buenas prácticas de programación';
                $upper_string = false;
            break;
            case 'fecha':
                $fecha_emision = date('Y-m-d H:i:s');
                $text = Carbon::parse($fecha_emision)->formatLocalized($e_dinamic['id_formato']);
                if(str_contains($e_dinamic['id_formato'],'de')){
                    $text = str_replace($this->dias_EN, $this->dias_ES, $text);
                    $text = str_replace($this->meses_EN, $this->meses_ES, $text);
                }
                $upper_string = false;
            break;
        }
        if($upper_string){
            return ucwords(mb_strtolower($text));
        }
        return $text;
    }

    public function destroy(Diploma $diploma)
    {
        $diploma->delete();
        return $this->success(['msg' => 'Diploma eliminada correctamente.']);
    }

    public function status(Diploma $diploma, Request $request)
    {
        $diploma->update(['active' => !$diploma->active]);
        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function downloadCertificate($id_user, $curso_id)
    {
        try {
            
            $data = $this->getDiplomaCursoData($id_user, $curso_id);

            if (!$data['old_template']) {
                $data['image'] = $this->get_diploma($pathImage, $dObjects, $backgroundInfo, $data);
            }

            return view('certificate_template', compact('data'));

        } catch (\Throwable $th) {

            $errorMessage = 'Este diploma no está disponible. Contacta con tu supervisor o soporte de la plataforma.';
            return view('error', compact('errorMessage'));
        }
    }

    private function getDiplomaCursoData($user_id, $course_id)
    {
        $user = User::with('subworkspace')
            ->select('id', 'name', 'surname', 'lastname', 'subworkspace_id')
            ->where('id', $user_id)->first();

        if (!$user) abort(404);

        $course = Course::with([
            'compatibilities_a:id',
            'compatibilities_b:id',
            'summaries' => function ($q) use ($user_id) {
                $q
                    ->with('status:id,name,code')
                    ->where('user_id', $user_id);
            },
        ])
            ->select('id', 'name', 'plantilla_diploma', 'show_certification_date', 'certificate_template_id')
            ->where('id', $course_id)->first();

        $course_to_export = $course;

        if (!$course && request()->has('original_id')) {

            $original_id = request()->original_id;
            $validate_compatible = $this->validateCompatibleCourse($user, $course, $original_id);

            if ($validate_compatible)
                $course_to_export = $validate_compatible;
        }

        $summary_course = SummaryCourse::getCurrentRow($course, $user);

        if (!$summary_course?->certification_issued_at) abort(404);

        // Load editable template from course,
        // school or module
        // ----------------------------------------

        $certificateTemplateId = $course_to_export->certificate_template_id;

        if (!$certificateTemplateId) {
            $school = $course_to_export->schools()->first();
            $certificateTemplateId = $school->certificate_template_id;
        }

        if (!$certificateTemplateId && $user->subworkspace->plantilla_diploma) {
            $certificateTemplateId = $user->subworkspace->certificate_template_id;
        }

        $editableTemplate = Certificate::find($certificateTemplateId);

        if ($editableTemplate) {
            $backgroundInfo = json_decode($editableTemplate->info_bg);
            $dObjects = json_decode($editableTemplate->d_objects);
            $pathImage = $editableTemplate->path_image;

            // $backgroundImage = $this->parse_image($editableTemplate->path_image, true);

        } else {

            // when course, school and module do not have
            // an editable template, load old certificate image
            // ----------------------------------------

            $plantilla_diploma = '';

            if ($course_to_export->plantilla_diploma) {
                $plantilla_diploma = $course_to_export->plantilla_diploma;
            }
            if (!$plantilla_diploma) {
                $school = $course_to_export->schools()->first();
                ($school && $school->plantilla_diploma) && $plantilla_diploma = $school->plantilla_diploma;
            }
            if (!$plantilla_diploma && $user->subworkspace->plantilla_diploma) {
                $plantilla_diploma = $user->subworkspace->plantilla_diploma;
            }

            $base64 = $this->parse_image($plantilla_diploma);
        }

        $fecha = $summary_course->certification_issued_at;

        return array(
            'old_template' => !$editableTemplate,
            'show_certification_date' => $course_to_export->show_certification_date,
            'courses' => removeUCModuleNameFromCourseName($course_to_export->name),
            'grade' => (int) $summary_course->grade_average,
            'users' => $user->fullname,
            'fecha' => $fecha,
            'image' => $base64 ?? NULL,
            'backgroundInfo' => $backgroundInfo ?? [],
            'dObjects' => $dObjects ?? [],
            'pathImage' => $pathImage ?? null,
            
        );
    }

    private function parse_image($plantilla, $calculateSize = false)
    {
        $type = pathinfo($plantilla, PATHINFO_EXTENSION);
        $plantilla = str_replace(" ", "%20", $plantilla);

        $headers = get_headers(get_media_url($plantilla));
        if ($headers && strpos($headers[0], "200 OK") !== false) {
            $image = file_get_contents(get_media_url($plantilla));
            // Return image's base64 and dimensions

            if ($calculateSize) {
                $imageBinary = imagecreatefromstring($image);
                $width = imagesx($imageBinary);
                $height = imagesy($imageBinary);

                return [
                    'width' => $width,
                    'height' => $height,
                    'base64' => 'data:image/' . $type . ';base64,' . base64_encode($image)
                ];

            } else if ($image !== false) {
                return 'data:image/' . $type . ';base64,' . base64_encode($image);
            }
        }

        return  abort(404);
    }
}
