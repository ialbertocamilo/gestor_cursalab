<?php

use Carbon\Carbon;
use Aws\S3\S3Client;
use App\Models\Workspace;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Str;
use App\Services\FileService;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use App\Models\Taxonomy;

const INACTIVE = false;
const ACTIVE = true;

const DAY_MINUTES = 1440;
const WEEK_MINUTES = 10080;
const MONTH_MINUTES = 43200;

const EV_QUALIFIED = 4578;

const COURSE_STATUS_APROBADO = 4568;
const COURSE_STATUS_DESARROLLO = 4569;
const COURSE_STATUS_DESAPROBADO = 4570;
const COURSE_STATUS_ENC_PENDIENTE = 4571;

const CACHE_MINUTES_DASHBOARD_GRAPHICS = 30;
const CACHE_SECONDS_DASHBOARD_GRAPHICS = 1800;

const CACHE_MINUTES_DASHBOARD_DATA = 60;
const CACHE_SECONDS_DASHBOARD_DATA = 3600;

const SECRET_PASS = '';

function generateSignedUrl(string $key, string $expires = '+360 minutes'): string
{
    $config = config('filesystems.disks.s3');

    $s3Client = new S3Client([
        'version' => 'latest',
        'region' => $config['region'],
        'credentials' => [
            'key' => $config['key'],
            'secret' => $config['secret'],
        ],
        'endpoint'    => 'https://sfo2.digitaloceanspaces.com',
        'options' => [
            'CacheControl' => 'max-age=25920000, no-transform, public',
        ]
    ]);

    $bucket = $config['scorm']['bucket'];

    $cmd = $s3Client->getCommand('GetObject', [
        'Bucket' => $bucket,
        'Key' => $key,
    ]);

    $request = $s3Client->createPresignedRequest($cmd, $expires);

    return (string) $request->getUri();
}

function reportsSignedUrl(string $key = '', string $expires = '+60 minutes'): string
{
    $config = config('filesystems.disks.s3');

    $s3Client = new S3Client([
        'version' => 'latest',
        'region' => $config['region'],
        'credentials' => [
            'key' => $config['key'],
            'secret' => $config['secret'],
        ],
        'endpoint'    => 'https://sfo2.digitaloceanspaces.com',
        'options' => [
            'CacheControl' => 'max-age=25920000, no-transform, public',
        ]
    ]);

    $bucket = $config['bucket'];
    $key = $config['root'] . '/' . $key;

    $cmd = $s3Client->getCommand('GetObject', [
        'Bucket' => $bucket,
        'Key' =>  $key,
    ]);

    $request = $s3Client->createPresignedRequest($cmd, $expires);

    return (string) $request->getUri();
}

function cleanExtraSpaces($str)
{
    return trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $str)));
}

function log_marker($title)
{
    $sep = '-------------';
    $mem = memory_get_usage();

    info($sep . $title . $sep);
    info('RAM: ' . convert($mem));
    info($sep . $sep);
}

function convert($number)
{
    $total = $number > 0 ? ($number / 1024) / 1024 : 0;

    return number_format($total) . "MB";
}

/**
 * Remove HTML tags and limit string to characters count
 *
 * @param string|null $text
 * @param int $limit
 * @return string
 */
function clean_html(?string $text, int $limit = 100)
{
    $text = html_entity_decode(strip_tags($text));

    return mb_substr($text, 0, $limit);
}

function secret($value)
{
    return openssl_encrypt($value, "AES-128-ECB", SECRET_PASS);
}

function reveal($value)
{
    return openssl_decrypt($value, "AES-128-ECB", SECRET_PASS);
}


function array_sum_column($array, $column)
{
    return array_sum(array_column($array, $column));
}

// function _data( $key )
// {
//     return config('data.' . $key);
// }

// function _data_ev($data_key, $array_key)
// {
//     $array = _data($data_key);

//     return equivalent_value($array, $array_key);
// }

function _ucwords($string)
{
    return mb_convert_encoding(mb_convert_case($string, MB_CASE_TITLE), "UTF-8");
}

function equivalent_value($array, $key, $default = NULL)
{
    return isset($array[$key]) ? $array[$key] : $default;
}


function array_replace_key($search, $replace, array $subject)
{
    $updatedArray = [];

    foreach ($subject as $key => $value) {
        if (!is_array($value) && $key == $search) {
            $updatedArray = array_merge($updatedArray, [$replace => $value]);

            continue;
        }

        $updatedArray = array_merge($updatedArray, [$key => $value]);
    }

    return $updatedArray;
}

function array_value_filter($array, $filterValues = [])
{

    return \Arr::where($array, function ($value) use ($array) {

        return array_search($value, $array) ? $value : null;
    });
}

function exist($value): bool
{
    return isset($value) && !empty($value);
}

function is_date($str): bool
{
    if (!$str) return false;
    if (is_array($str)) return false;

    $str = str_replace('/', '-', $str);

    return is_numeric(strtotime($str));
}

function delete_col(&$array, $key)
{
    return array_walk($array, function (&$v) use ($key) {
        unset($v[$key]);
    });
}


function acl($slug, $field)
{
    $permiso = Permiso::getBySlug($slug);

    return $permiso->$field;
}

function _validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function bytesToMB($bytes)
{
    return round(($bytes / 1048576), 2);
}

function space_url($path)
{
    return FileService::generateUrl($path);
}

function thousandsFormat($num)
{
    if ($num >= 1000) {

        $x = round($num);
        $x_number_format = number_format($x);
        $x_array = explode(',', $x_number_format);
        $x_parts = array('K', 'M', 'B', 'T');
        $x_count_parts = count($x_array) - 1;
        $x_display = $x;
        $x_display = $x_array[0];
        // $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
        $x_display .= $x_parts[$x_count_parts - 1];

        return $x_display;
    }

    return $num;
}

function carbonFromFormat($date, $format = 'Y-m-d H:i:s'): Carbon
{
    return Carbon::createFromFormat($format, $date);
}

function catchInfo($title, $message)
{
    info($title);
    info($message);
}

function errorExceptionServer($code = '')
{
    return response()->json(['message' => 'Ha ocurrido un problema. Contáctate con el equipo de soporte.', 'code' => $code], 400);
}


function get_title_date($date)
{
    $days = config('meetings.days');
    $months = config('meetings.months');

    $day = $date->dayOfWeekIso;
    $month = $date->month;

    $dia = isset($days[$day]) ? $days[$day] : 'X';
    $mes = isset($months[$month]) ? $months[$month] : 'X';

    $numero = $date->format('d');

    return "{$dia}, {$numero} de {$mes}";
}

function get_current_workspace()
{
    if (request()->workspace_id) return Workspace::find(request()->workspace_id);
    if (session('workspace')) return session('workspace');
    return null;
}

function get_current_workspace_indexes(string $key = NULL)
{
    $currWorkspace = get_current_workspace();

    $currWorkspaceIndex = $currWorkspace->id;
    $currSubworkspacesIndexes = $currWorkspace->subworkspaces->pluck('id');

    $dynamicKeys = ['id' => $currWorkspaceIndex,
        'ids' => $currSubworkspacesIndexes];
    $stateKey = $dynamicKeys[$key] ?? true;

    return is_bool($stateKey) ? $dynamicKeys : $stateKey;
}

function get_current_subworkspaces()
{
    $workspace = get_current_workspace();
    $subworkspaces = [];

    if ($workspace) {

        if (auth()->user()->isAn('super-user')) {

            $subworkspaces = $workspace->subworkspaces()->get();

        } else {

            $subworkspaces = auth()->user()->subworkspaces()->where('parent_id', $workspace->id)->get();
        }
    }

    return $subworkspaces;
}

function get_subworkspaces($workspace)
{
    $subworkspaces = [];

    if ($workspace) {

        if (auth()->user()->isAn('super-user')) {

            $subworkspaces = $workspace->subworkspaces()->get();

        } else {

            $subworkspaces = auth()->user()->subworkspaces()->where('parent_id', $workspace->id)->get();
        }
    }

    return $subworkspaces;
}

function get_subworkspaces_id($workspace)
{
    $workspace = is_int($workspace) ? Workspace::find($workspace) : $workspace;
    $subworkspaces = get_subworkspaces($workspace);

    if ($subworkspaces) {
        return $subworkspaces->pluck('id')->toarray();
    }

    return [];
}

function current_subworkspaces_id($field = 'id')
{
    $subworkspaces = get_current_subworkspaces();

    if ($subworkspaces) {
        return $subworkspaces->pluck($field)->toarray();
    }

    return [];
}

function cache_clear_model($model)
{
    \Artisan::call('modelCache:clear', array('--model' => $model));
}

function get_media_url($path = '', $cdn = 'cdn')
{
    return FileService::generateUrl($path, $cdn);
}

function get_media_root_url($cdn = 'cdn')
{
    return FileService::getRootUrl($cdn);
}

function excelDateToDate($fecha)
{
    try {
        if (_validateDate($fecha, 'Y-m-d')) {
            return $fecha;
        }
        if (_validateDate($fecha, 'Y/m/d') || _validateDate($fecha, 'd/m/Y') || _validateDate($fecha, 'd-m-Y')) {
            // return date("d/m/Y",$fecha);
            return Carbon::parse($fecha)->format('Y-m-d');
        }
        $php_date = $fecha - 25569;
        $date = date("Y-m-d", strtotime("+$php_date days", mktime(0, 0, 0, 1, 1, 1970)));
        return $date;
    } catch (\Exception $e) {
        return null;
    }
}

function removeUCModuleNameFromCourseName($course_name): string
{
    $name = $course_name;

    if (str_contains($course_name, "Capacitación Mifarma - "))
        $name = str_replace("Capacitación Mifarma - ", "", $course_name);

    if (str_contains($course_name, "Capacitación Inkafarma - "))
        $name = str_replace("Capacitación Inkafarma - ", "", $course_name);

    if (str_contains($course_name, "Capacitación Farmacias Peruanas - "))
        $name = str_replace("Capacitación Farmacias Peruanas - ", "", $course_name);

    return $name;
}

function stringContains($string, $q)
{
    // if (!$q) return false;
    return false !== stripos($string, $q);
}

function stringConcatEqualNum(array $data, int $num)
{
    $piecesJoin = implode('', $data);
    $piecesPart = str_split($piecesJoin, $num);
    $pieceIndex = floor(strlen($piecesJoin) / $num);
    unset($piecesPart[$pieceIndex]);

    return implode('|', $piecesPart);
}

function get_data_bykeys($data, $keys = [])
{
    $new_data = [];
    foreach ($data as $key => $value) {
        if(in_array($key, $keys)) $new_data[$key] = $value;
    }

    return $new_data;
}

function messageToSlackByChannel($texto,$attachments,$canal){
    $blocks =  [
        "text" => "*".$texto."*",
        "attachments"  => $attachments
	];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $canal);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"text\":\"$mensaje\"}");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($blocks));

    // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text'=>$mensaje]));
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    info($result);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
}

function calculateValueForQualification($value, $current_system, $main_system = 20)
{
    if ($current_system == $main_system) return $value;

    $new_value = $value * $current_system / $main_system;

    return round($new_value, 2);
}

function transform_domain(array $ArrayHost)
{
    $arrayAvailableDomains = [ 'youtu' => 'youtube',
                               'youtube' => 'youtube',
                               'vimeo' => 'vimeo'];
    $currentType = NULL;

    foreach($arrayAvailableDomains as $key => $value) {
        if(in_array($key, $ArrayHost)) {
            $currentType = $value;
        }
    }

    return $currentType;
}

function get_type_link(string $linked, string $key = 'type')
{
    $parsedLinked = parse_url($linked);
    [ 'host' => $host , 'path' => $path ] = $parsedLinked;

    $currentType = transform_domain( explode('.', $host) );
    $currentHash = NULL;

    switch($currentType) {
        case 'youtube':
            $query = $parsedLinked['query'] ?? NULL;
            $currentHash = ($query) ? explode('=', $query)[1] : explode('/', $path)[1];
        break;
        case 'vimeo':
            $currentHash = explode('/', $path)[1];
        break;
    }

    $switchKey = [ 'type' => $currentType,
                   'hash' => $currentHash ];

    return $switchKey[$key];
}

function get_type_link2(string $linked, string $key = 'type')
{
    $currentType = is_numeric($linked) ? 'vimeo' : 'youtube';

    $switchKey = [ 'type' => $currentType,
                   'hash' => $linked ];

    return $switchKey[$key];
}

function getExtensionFileUrl(string $url) {
    ['path' => $filePath] = parse_url($url);

    $prevPath = explode('.', $filePath);
    $prevExtension = $prevPath[count($prevPath) - 1];
    $existScorm = ($prevExtension === 'html'); // type scorm

    $fileExtension = $existScorm ? 'scorm' : $prevExtension;
    return $fileExtension;
}

function get_type_media(string $media)
{
    $fileExtension = getExtensionFileUrl($media);

    $arrayAvailableTypes = [
                             'image' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'],
                             'video' => ['mp4', 'webm', 'mov'],
                             'audio' => ['mp3'],
                             'pdf'   => ['pdf'],
                             'scorm' => ['zip', 'scorm'],
                             // 'excel' => ['xls', 'xlsx', 'csv'],
                             'office' => ['xls', 'xlsx', 'csv','ppt', 'pptx', 'doc', 'docx']
                          ];

    foreach($arrayAvailableTypes as $key => $value) {
        if(in_array(strtolower($fileExtension), $value)) {
            $currentType = $key;
        }
    }

    return $currentType;
}
function formatSize($kilobytes, $precision = 2, $parsed = true) { // desde KB hacia arriba
    $unit = ["Kb", "Mb", "Gb", "Tb", "Pt"];
    $exp = floor(log($kilobytes, 1024)) | 0;

    $size = round($kilobytes / (pow(1024, $exp)), $precision);
    $size_unit = $unit[$exp];
    if ($parsed) {
        return $size.' '.$size_unit;
    }

    return compact('size', 'size_unit');
}

/*
    count: valor variable,
    total: valor total al 100%,
    limite: solo para exceded
*/
function calculate_porcent($count, $total, int $limite = 90)
{
    $porcent = 0;
    $exceded = false;

    if($total > 0) {
        $porcent = round($count * 100 / $total);
        $exceded = $porcent >= $limite;
    }

    return compact('porcent', 'exceded');
}


function is_cursalab_superuser($only_test_environment = true)
{
    $right_environment = $only_test_environment ? config('app.test_environment') : true;
    $is_superuser = auth()->user()->isAn('super-user');
    $email = auth()->user()->email_gestor;

    return $is_superuser && str_contains($email, '@cursalab.io') && $right_environment;
}

function fechaCastellano($fecha)
{
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $nombredia = str_replace($dias_EN, $dias_ES, $dia);
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    // return "Lima, ".$numeroDia." de ".$nombreMes." de ".$anio;
    return $numeroDia . " de " . $nombreMes . " del " . $anio;
}
function fechaCastellanoV2($date_string)
{
    // $date_string = 'Lunes, 5 Agosto';
    $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $date_string = str_replace($dias_EN, $dias_ES, $date_string);
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $date_string = str_replace($meses_EN, $meses_ES, $date_string);
    // return "Lima, ".$numeroDia." de ".$nombreMes." de ".$anio;
    return $date_string;
}

function getCriterionValue($id, $criteria) {
    $criterion = $criteria->where('criterion_id', $id)->first();

    return $criterion
        ? $criterion->value_text
        : '';
}

function extractYoutubeVideoCode(string $url): ?string
{
    if (!str_contains($url, 'https://')) return $url;

    $matches = preg_split('/(vi\/|v%3D|v=|\/v\/|youtu\.be\/|\/embed\/)/', $url);

    if (!isset($matches[1])) {
        return null;
    }

    $match = $matches[1];
    $split = preg_split('/[^\w-]/i', $match);

    if (!isset($split[0])) {
        return null;
    }

    return $split[0];
}

function extractVimeoVideoCode(string $url): ?string
{
    if (!str_contains($url, 'https://')) return $url;

    $regex = '~
        # Match Vimeo link and embed code
        (?:<iframe [^>]*src=")?              # If iframe match up to first quote of src
        (?:                                  # Group vimeo url
                https?:\/\/                  # Either http or https
                (?:[\w]+\.)*                 # Optional subdomains
                vimeo\.com                   # Match vimeo.com
                (?:[\/\w:]*(?:\/videos)?)?   # Optional video sub directory this handles groups links also
                \/                           # Slash before Id
                ([0-9]+)                     # $1: VIDEO_ID is numeric
                [^\s]*                       # Not a space
        )                                    # End group
        "?                                   # Match end quote if part of src
        (?:[^>]*></iframe>)?                 # Match the end of the iframe
        (?:<p>.*</p>)?                       # Match any title information stuff
        ~ix';

    preg_match( $regex, $url, $matches );

    return $matches[1];
}

function db_raw_dateformat($field, $alias = null, $format = "'%d/%m/%Y %H:%i'")
{
    $alias = $alias ?? $field;

    return \DB::raw("DATE_FORMAT({$field}, $format) as {$alias}");
}

function generate_qr_code_in_base_64($text,$height,$width,$scaleX=1,$scaleY=1){
    $texto = "http://ejemplo.com";
    $newwidth = $width * $scaleX;
    $newheight = $height * $scaleY;
    $writer = new PngWriter();
    $qrCode = QrCode::create($text)
    ->setEncoding(new Encoding('UTF-8'))
    ->setSize($newwidth)
    ->setMargin(30*$scaleX);
    $result = $writer->write($qrCode)->getDataUri();
    return $result;
}
function currentPlatform(){
    $session_platform = session('platform');
    $platform_code = $session_platform && $session_platform == 'induccion' ? 'onboarding' : 'training';
    $platform = Taxonomy::getFirstData('project', 'platform', $platform_code);
    return $platform;
}

function verifyBooleanValue($value){
    return ($value === 'true' or $value === true or $value === 1 or $value === '1');
}