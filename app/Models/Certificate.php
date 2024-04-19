<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use softdeletes;

    protected $fillable = ['media_id', 'title', 'font_id', 'path_file', 'info_bg', 'd_objects', 's_objects', 'active','path_image', 'platform_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    private $dias_ES = array("lunes", "martes", "miércoles", "jueves", "viernes", "sábado", "domingo");
    private $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    private $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    private $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

    public function scopeFilterByPlatform($q){
        $platform = session('platform');
        $type_id = $platform && $platform == 'induccion'
                    ? Taxonomy::getFirstData('project', 'platform', 'onboarding')->id
                    : Taxonomy::getFirstData('project', 'platform', 'training')->id;
        $q->where('platform_id',$type_id);
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    protected function search($request, $paginate = 10) {
        $workspace = get_current_workspace();

        $q = self::query()->FilterByPlatform()->withWhereHas('media', function($query) use($workspace) {
            $query->where('workspace_id', $workspace->id);
        });

        if ($request->q)
            $q->where('title', 'like', "%$request->q%");

        if ($request->active)
            $q->where('active', $request->active);

        $sort = ($request->sortDesc == 'true') ? 'DESC' : 'ASC';

        if($request->sortBy)
            $q->orderBy($request->sortBy, $sort);
        else
            $q->orderBy('created_at', 'DESC');

        return $q->paginate($request->paginate);
    }

    protected function getBasesFromImages($edit_plantilla)
    {
        $images_bases = [];
        $images_bases = $edit_plantilla['s_objects_images'];
        $images_bases[] = $edit_plantilla['s_object_bg'];

        return collect($images_bases)->map(function ($item) {
            return [
                'media_id' => $item['media_id'],
                'src' => $item['src'],
            ];
        });
    }


    protected function pushTypeIText($e_static)
    {
        $data = get_data_bykeys($e_static, ['type', 'text', 'left','top',  'fill', 'textAlign',
            // adicional
            'fontStyle', 'fontFamily', 'fontWeight', 'fontSize',
            // 'width', 'height',
            // 'lineHeight', 'zoomX', 'originX', 'originY'
        ]);
        return $data;
    }

    protected function compareBases64($static, $bases_data)
    {
        $base_main = explode(',', $static, 2);
        $base_main = $base_main[1];

        $media_id = NULL;

        foreach ($bases_data as $base) {
            $base_current = explode(',', $base['src'], 2);
            $base_current = $base_current[1];

            if($base_main === $base_current) {
                $media_id = $base['media_id'];
                break;
            }
        }

        return $media_id;
    }


    protected function pushTypeImage($e_static, $nombre_plantilla,
                                      $compare = false, $bases64_compare = []) {
        $data = get_data_bykeys($e_static, ['type', 'left','top', 'scaleX', 'scaleY'
            // adicional
            // 'width', 'height',
            // 'flipX', 'flipY', 'skewX', 'skewY'
        ]);

        $nombre_plantilla_final = Media::generateNameFile($nombre_plantilla, 'jpg');
        $path = 'images/diplomas/'.$nombre_plantilla_final;

        // comparar los bases
        if($compare){
            $media_id = self::compareBases64($e_static['src'], $bases64_compare);

            // si es igual al base
            if($media_id) {
                $data['media_id'] = $media_id;
                return $data;
            }
        }

        $media = self::uploadMediaBase64($nombre_plantilla_final, $path, $e_static['src']);
        $data['media_id'] = $media->id;

        // info(['msg' => 'create a media', 'media_id' => $media->id]);
        return $data;
    }

    protected function uploadMediaBase64($name, $path, $base64)
    {
        $exploded = explode(',', $base64, 2);
        $s3 = Storage::disk('s3')->put($path, base64_decode($exploded[1]), 'public');
        $size = Storage::disk('s3')->size($path);

        try {
            $save_size = round(($size / 1024) / 1024);
        } catch (\Exception $exception) {
            $save_size = 0;
        }

        $media = new Media;
        $media->title = $name;
        $media->file = $path;
        $media->ext = 'jpg';
        $media->size = $size;
        $media->workspace_id = session('workspace')['id'] ?? NULL;
        $media->save();

        return $media;
    }

    protected function getTotalByUser($user = null, $assigned_courses = null)
    {
        $user = $user ?? auth()->user();

        $user_courses = $assigned_courses ?? $user->getCurrentCourses(withRelations: 'soft');
        $user_courses_id = $user_courses->pluck('id');
        $user_compatibles_courses_id = $user_courses->whereNotNull('compatible')->pluck('compatible.course_id');
        $all_courses_id = $user_courses_id->merge($user_compatibles_courses_id);

        $query = SummaryCourse::query()
            ->where('user_id', $user->id)
            ->whereIn('course_id', $all_courses_id->toArray())
            ->whereNotNull('certification_issued_at');

        // if ($request->type == 'accepted')
        //     $query->whereNotNull('certification_accepted_at');

        // if ($request->type == 'pending')
        //     $query->whereNull('certification_accepted_at');

        $certificates = $query->get();

        $total = 0;

        // $qs = $request->q ?? NULL;

        foreach ($user_courses as $user_course) {

            // if ($qs AND !stringContains($user_course->name, $qs))
            //     continue;

            $certificate = $certificates->where('course_id', $user_course->id)->first();

            if ($certificate) {

                $total++;

                continue;
            }

            if ($user_course->compatible) {

                $compatible_certificate = $certificates->where('course_id', $user_course->compatible->course_id)->first();

                if ($compatible_certificate) {
                    $total++;
                }
            }
        }

        return $total;
    }

    protected function calculateTextBox($font_size, $font_angle, $font_file, $text)
    {
        $box   = imagettfbbox($font_size, $font_angle, $font_file, $text);

        if( !$box ) return false;

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

    public function getImageResized($image, int $newWidth, int $newHeight)
    {
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

    protected function convertHexadecimalToRGB($hex)
    {
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

    protected function image_create($data,$scaleX,$scaleY,$width,$height)
    {
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

    protected function hex2rgb($hex)
    {
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

    protected function jpg_to_base64($data)
    {
        ob_start(); // Let's start output buffering.
            imagejpeg($data); //This will normally output the image, but because of ob_start(), it won't.
            $contents = ob_get_contents(); //Instead, output above is saved to $contents
        ob_end_clean(); //End the output buffer.
        $dataUri = "data:image/jpeg;base64," . base64_encode($contents);
        return $dataUri;
    }

    protected function get_text($e_dinamic, $real_data = [])
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
            case 'processes':
                $text = $real_data['processes'] ?? 'Proceso de inducción';
                $upper_string = false;
            break;
            case 'fecha':
                if (isset($real_data['fecha']) && $real_data['fecha']) {
                    $text = $real_data['fecha']->formatLocalized($e_dinamic['id_formato']);
                } else {
                    $fecha_emision = date('Y-m-d H:i:s');
                    $text = Carbon::parse($fecha_emision)->formatLocalized($e_dinamic['id_formato']);
                }

                if(str_contains($e_dinamic['id_formato'],'de')){
                    $text = str_replace($this->dias_EN, $this->dias_ES, $text);
                    $text = str_replace($this->meses_EN, $this->meses_ES, $text);
                }
                $upper_string = false;
            break;
        }

        if ($upper_string) {
            return ucwords(mb_strtolower($text));
        }

        return $text;
    }

    protected function parse_image($plantilla, $calculateSize = false)
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
    protected function saveFont($data){
        $font_name = $data['name'];
        $_taxonomy_font_parent = new Taxonomy();
        $_taxonomy_font_parent->group = 'certificate';
        $_taxonomy_font_parent->type = 'font';
        $_taxonomy_font_parent->name = $font_name;
        $_taxonomy_font_parent->active = 1;
        $_taxonomy_font_parent->save();
        $fonts = ['font-normal','font-bold','font-italic','font-bold-italic'];
        $_taxonomy_font_child = [];
        foreach ($fonts as $font) {
            if(isset($data[$font]) && $data[$font] && $data[$font] != 'null'){
                $path_font =  Media::uploadFile($data[$font],null, false, 'ttf', 'font');
                $_taxonomy_font_child[] = [
                    'group'=> 'certificate',
                    'type'=> 'font',
                    'code' => $font,
                    'name' => Str::slug($font_name).'-'.$font,
                    'active'=>1,
                    'parent_id' => $_taxonomy_font_parent->id,
                    'path'=> $path_font,
                    'extra_attributes'=> json_encode([
                        'storage' => 's3'
                    ]),
                ];
            }
        }
        if(count($_taxonomy_font_child)){
            Taxonomy::insert($_taxonomy_font_child);
        }
    }
    protected function storeRequest($title, $background, $objects, $certificate = null, $images_base64 = null,$font_id=null)
    {
        $e_statics = $objects->where('static', true);
        $e_dinamics = $objects->where('static', false);

        $compare = $certificate ? true : false;
        //Custom FONTS
        $custom_font = false;
        $list_type_fonts = [];
        if($font_id){
            $custom_font = true;
            $list_type_fonts = Taxonomy::select('code','path','extra_attributes')->where('group','certificate')->where('type','font')->where('parent_id',$font_id)->get();
        }
        $local_paths_font = [];

        if ($background) {

            $im = $this->image_create($background['src'], 1, 1, 1, 1);
            $x = $background['left'];
            $y = $background['top'];

            $nombre_plantilla = 'plantilla-'. Str::slug($title);
            $info_s_objects = []; // s_objects[]

            foreach ($e_statics as $e_static) {

                $e_static_type = $e_static['type'];

                switch ($e_static_type) {

                    case 'i-text':
                        //  === para el font ===
                        $font = self::getPathFonth($e_static,$custom_font,$list_type_fonts);
                        // $font = realpath('.').'/fonts/diplomas/'.$fontName;
                        //  === para el font ===

                        $rgb = Certificate::hex2rgb($e_static['fill']);
                        $color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
                        $lines = preg_split('/\n|\r/',$e_static['text']);

                        // resize font;
                        $divideSize = $e_static['fontSize'] / 3.3;
                        $fontsize = $e_static['fontSize'] - $divideSize;
                        
                        imagettftext(
                            $im,
                            $fontsize,
                            0,
                            $e_static['left'] - $x,
                            $e_static['top'] - $y + $fontsize,
                            $color,
                            $font,
                            utf8_decode($e_static['text'])
                        );
                        
                        $info_s_objects[] = Certificate::pushTypeIText($e_static);
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
                        $info_s_objects[] = Certificate::pushTypeImage($e_static, $nombre_plantilla, $compare, $images_base64);
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
                if($e_dinamic['type']=='qr_image'){
                    $info_d_objects [] = [
                        'id'=>'qr-validator',
                        'type'=>$e_dinamic['type'],
                        'fill'=>$e_dinamic['fill'],
                        'left'=>$e_dinamic['left'],
                        // 'left_calc'=>$e_dinamic['left'] - $x,
                        'top'=>$e_dinamic['top'],
                        // 'top_calc'=>$e_dinamic['top'] - $y,
                        'height'=>$e_dinamic['height'],
                        'width'=>$e_dinamic['width'],
                        'scaleX' => $e_dinamic['scaleX'],
                        'scaleY' => $e_dinamic['scaleY'],
                        'zoomX'=> $e_dinamic['zoomX'] ?? null,
                    ];
                }
            }
            // === guarda imagen - media y retorna id ===
            $info_bg = Certificate::pushTypeImage($background, $nombre_plantilla, $compare, $images_base64);

            $nombre_plantilla_final = $certificate ?
                Media::generateNameFile($nombre_plantilla, 'jpg') :
                $nombre_plantilla.'-'.rand(0,1000).'.jpg';

            $path = 'images/'.$nombre_plantilla_final;
            $preview = $this->jpg_to_base64($im);

            // === guarda imagen - media ===
            $media = Certificate::uploadMediaBase64($nombre_plantilla_final, $path, $preview);

            $platform = session('platform');
            $platform_type_id = $platform && $platform == 'induccion'
                        ? Taxonomy::getFirstData('project', 'platform', 'onboarding')?->id
                        : Taxonomy::getFirstData('project', 'platform', 'training')?->id;

            // === guarda diploma ===
            $diploma = $certificate ?? new Certificate;
            $diploma->media_id = $media->id;
            $diploma->title = $title;
            $diploma->font_id = $font_id;
            $diploma->path_image = $path;
            $diploma->info_bg = json_encode($info_bg);
            $diploma->s_objects = json_encode($info_s_objects);
            $diploma->d_objects = json_encode($info_d_objects);
            $diploma->platform_id = $platform_type_id ?? null;

            $diploma->save();

            return $diploma->id ?? true;
        }

        return false;
    }

    protected function remove_emoji($text)
    {
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

    protected function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
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

    protected function setDynamicsToImage($image, $e_dinamics, $background, $real_info = [],$font_id=null)
    {
        $x = $background['left'];
        $y = $background['top'];
        //Custom FONTS
        $custom_font = false;
        $list_type_fonts = [];
        if($font_id){
            $custom_font = true;
            $list_type_fonts = Taxonomy::select('code','path','extra_attributes')->where('group','certificate')->where('type','font')->where('parent_id',$font_id)->get();
        }
        $local_paths_font = [];
        foreach ($e_dinamics as $e_dinamic)
        {
             //  === para el font ===
             
            if($e_dinamic['type']=='text'){
                $rgb = Certificate::hex2rgb($e_dinamic['fill']);
                $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
                $text = Certificate::get_text($e_dinamic, $real_info);
                $text = wordwrap($text, 40, "multiline");

                $fontsize =  $e_dinamic['fontSize'];

                if (isset($e_dinamic['zoomX']) && $e_dinamic['zoomX']) {
                    $fontsize =  $fontsize - ($fontsize * $e_dinamic['zoomX']);
                }

                $left = $e_dinamic['left'] - $x;
                $top = $e_dinamic['top'] - $y + $fontsize;

                

                 //  === para el font ===
                 $font = self::getPathFonth($e_dinamic,$custom_font,$list_type_fonts);

                //Eliminar emogis
                $text = trim(Certificate::remove_emoji($text));
                //Centrado multilinea
                $explode_text = explode('multiline', $text);

                foreach ($explode_text as $e_text) {

                    if(isset($e_dinamic['centrado']) && ($e_dinamic['centrado']=="true")){
                        $calculateTextBox = Certificate::calculateTextBox($fontsize, 0, $font, trim($e_text));
                        $left = ($background['image_width']/2) - (($calculateTextBox['width']/2));
                    }

                    imagettftext($image,$fontsize,0 ,$left,$top , $color, $font,utf8_decode(trim($e_text)));
                    $top = $top + $fontsize + (0.2*$fontsize);
                }
            }
            if($e_dinamic['type']=='qr_image'){
                $user_id = $real_info['user_id'] ?? 216;
                $course_id = $real_info['course_id'] ?? 1312;
                $text_qr = env('APP_URL').'/tools/ver_diploma/'.$user_id.'/'.$course_id;
                $qr_code_string = generate_qr_code_in_base_64($text_qr,$e_dinamic['height'],$e_dinamic['width'],$e_dinamic['scaleX'],$e_dinamic['scaleY']);
                $image2 = self::image_create($qr_code_string, $e_dinamic['scaleX'], $e_dinamic['scaleY'], $e_dinamic['width'], $e_dinamic['height']);
                self::imagecopymerge_alpha(
                    $image, // destino base
                    $image2, // fuente base
                    $e_dinamic['left']-$x,
                    $e_dinamic['top']-$y,
                    0,
                    0,
                    $e_dinamic['width']*$e_dinamic['scaleX'],
                    $e_dinamic['height']*$e_dinamic['scaleY'],
                    100);
            }
        }

        return $image;
    }
    protected function duplicateCertificatesFromWorkspace($current_workspace,$new_workspace){
        $certificates = Certificate::select('media_id', 'title',  'info_bg','font_id','d_objects', 's_objects', 'active')
        ->withWhereHas('media', function($query) use($current_workspace){
            $query->select('id','title', 'description', 'file', 'ext', 'external_id', 'size', 'workspace_id')
            ->where('workspace_id', $current_workspace->id);
        })->get();
        foreach ($certificates as $certificate) {
            $media = $certificate->media->toArray();
            $media['workspace_id'] = $new_workspace->id;
            unset($media->id);
            $_media = new Media();
            $_media->fill($media);
            $_media->save();
            $certificate = $certificate->toArray();
            $certificate['media_id'] = $_media->id;
            $_certificate = new Certificate();
            $_certificate->fill($certificate);
            $_certificate->save();
        }
    }
    protected function getPathFonth($element,$custom_font,$list_type_fonts){
        $font = '';
        if($custom_font && count($list_type_fonts)){
            $fontName = $list_type_fonts->where('code','font-normal')->first();
            if ($element['fontStyle'] === 'italic' && $element['fontWeight'] === 'bold') {
                $fontName = $list_type_fonts->where('code','font-bold-italic')->first() ?? $fontName;
            }else if($element['fontStyle'] === 'italic') {
                $fontName = $list_type_fonts->where('code','font-italic')->first() ?? $fontName;
            }else if($element['fontWeight'] === 'bold') {
                $fontName = $list_type_fonts->where('code','font-bold')->first() ?? $fontName;
            }
            if($fontName->extra_attributes['storage'] == 's3'){
                $s3FontUrl = get_media_url($fontName->path,'s3');
                $fontFilename = basename($fontName->path);
                $fileContents = file_get_contents($s3FontUrl);
                if(!Storage::disk('public')->exists($fontName->path)){
                    Storage::disk('public')->put($fontName->path, $fileContents);
                }
                $font = storage_path('app/public/' . $fontName->path);
                $local_paths_font[] = $font;
            }else{
                $font =  realpath('.').'/'.$fontName->path;
            }
        }else{
            $fontName = 'calisto-mt.ttf';
            if ($element['fontStyle'] === 'italic' && $element['fontWeight'] === 'bold') {
                $fontName = 'calisto-mt-bold-italic.ttf';
            }else if($element['fontStyle'] === 'italic') {
                $fontName = 'calisto-mt-italic.ttf';
            }else if($element['fontWeight'] === 'bold') {
                $fontName = 'calisto-mt-bold.ttf';
            }
            $font = realpath('.').'/fonts/diplomas/'.$fontName;
        }
        return $font;
    }
}
