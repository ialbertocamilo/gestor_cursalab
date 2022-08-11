<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

const INACTIVE = false;
const ACTIVE = true;

const DAY_MINUTES = 1440;
const WEEK_MINUTES = 10080;
const MONTH_MINUTES = 43200;

const CACHE_MINUTES_DASHBOARD_GRAPHICS = 30;
const CACHE_MINUTES_DASHBOARD_DATA = 60;

const SECRET_PASS = '';

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

    return substr(strip_tags($text), 0, $limit);
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

function delete_col(&$array, $key) {
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
    if ($path)
        return Storage::url($path);

    return '';
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
    return response()->json(['message' => 'Error de servidor.', 'code' => $code], 400);
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
