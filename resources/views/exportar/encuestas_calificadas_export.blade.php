<?php 
use App\Http\Controllers\HomeController;

$idg = $data['idg'];
$curso_id = $data['curso_id'];
$pgtas = $data['pgtas'];
$mod = $data['mod'];

$preguntas_arr = $data['preguntas_arr'];
$cursos_arr = $data['cursos_arr'];
$categorias_arr = $data['categorias_arr'];
//
$cab = array();
$criterios = array();
$data_pygs = array();
$btable = "";
//
$criterio_valor_x_rpta = array();
$cont_rptas = $tot_rptas = 0;

// dd($pgtas);
foreach ($pgtas as $item) {
    $data = HomeController::resumenCalifica($item->pregunta_id, $mod, $item->curso_id, $idg);
    if(isset($data) && isset($data['data_rptas'])){
        $data_pygs[$item->curso_id][$item->pregunta_id] = array('titulo' => $item->pregunta_id, 'data_rptas' => $data['data_rptas'] );
        $criterio_valor_x_rpta[$item->curso_id][$item->pregunta_id] = $data['criterio_valor_x_rpta'];
        $tot_rptas = $data['tot_rptas'];
    }
}
$cont_rptas = $tot_rptas;
// dd($data_pygs);
// dd($criterio_valor_x_rpta);
?>

@if (!empty($data_pygs))
    <?php //Log::info('Encuesta con data '); ?>
    @foreach($data_pygs as $cur_id => $preg_data)
        <table>
            <thead>
                <tr height="20" style='height:15.0pt'>
                    <td rowspan="2"><strong>Escuela</strong></td>
                    <td rowspan="2"><strong>Curso</strong></td>
                    <td rowspan="2"><strong>Pregunta</strong></td>
                    <td rowspan="2" ><strong>Promedio</strong></td>
                    <td rowspan="2" ><strong>Top</strong></td>
                    <td rowspan="2" ><strong>Bottom</strong></td>
                    <td>5</td>
                    <td>4</td>
                    <td>3</td>
                    <td>2</td>
                    <td>1</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr >
                    <td><strong>Muy Bueno</strong></td>
                    <td><strong>Bueno</strong></td>
                    <td><strong>Regular</strong></td>
                    <td><strong>Malo</strong></td>
                    <td><strong>Muy Malo</strong></td>
                    <td><strong>TOTAL</strong></td>
                    <td><strong>Muy Bueno</strong></td>
                    <td><strong>Bueno</strong></td>
                    <td><strong>Regular</strong></td>
                    <td><strong>Malo</strong></td>
                    <td><strong>Muy Malo</strong></td>
                    <td><strong>T2B</strong></td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @foreach($preg_data as $preg_id => $datos)
                    @foreach ($datos['data_rptas'] as $key2 => $rptas)
                        <?php 
                            $suma_cal   = 0;
                            $sum_t2b    = 0;
                            $sum_mb     = 0;
                            $sum_b      = 0;
                            $sum_r      = 0;
                            $sum_m      = 0;
                            $sum_mm     = 0;
                            $porc_mb = $porc_b = $porc_r = $porc_m = $porc_mm = 0;
                            if (isset($rptas)) {
                                foreach ($rptas as $key3 => $val3) {
                                    $suma_cal   = $rptas['suma_cal'];
                                    $sum_t2b    = $rptas['sum_t2b'];
                                    $sum_mb     = $rptas['sum_mb'];
                                    $sum_b      = $rptas['sum_b'];
                                    $sum_r      = $rptas['sum_r'];
                                    $sum_m      = $rptas['sum_m'];
                                    $sum_mm     = $rptas['sum_mm'];
                                }
                            }
                            $suma_total = $sum_mb + $sum_b + $sum_r + $sum_m + $sum_mm;
                            $porc_mb    = ($sum_mb / $suma_total) * 100;
                            $porc_b     = ($sum_b / $suma_total) * 100;
                            $porc_r     = ($sum_r / $suma_total) * 100;
                            $porc_m     = ($sum_m / $suma_total) * 100;
                            $porc_mm    = ($sum_mm / $suma_total) * 100;
                            $porc_t2b   = $porc_mb + $porc_b;
                            $sum_porc   = $porc_mb + $porc_b + $porc_r + $porc_m + $porc_mm;
                        ?>
                    
                        <tr>
                            <td>{{ isset($categorias_arr[$cur_id]) ? $categorias_arr[$cur_id] : "" }}</td>
                            <td>{{ isset($cursos_arr[$cur_id]) ? $cursos_arr[$cur_id] : "" }}</td>
                            <td>{{ isset($preguntas_arr[$preg_id]) ? $preguntas_arr[$preg_id] : "" }}</td>
                            <?php 
                                $suma_valores = 0; 
                                $contador = 0;
                                $top = 1;
                                $bottom = 5;
                                $prom = 0;
                            
                                if (isset($criterio_valor_x_rpta[$cur_id][$key2])) {
                                    foreach ($criterio_valor_x_rpta[$cur_id][$key2][$key2] as $valor){
                                        $suma_valores += $valor; 
                                        $contador += 1; 
                                        $top = ($top < $valor) ? $valor: $top;
                                        $bottom = ($bottom > $valor) ? $valor: $bottom;
                                    } 
                                    $prom = round(($suma_valores/$contador), 2);
                                }
                            ?>
                            <td>{{ $prom }}</td>
                            <td>{{ $top }}</td>
                            <td>{{ $bottom }}</td>
                            <td>{{ $sum_mb }}</td>
                            <td>{{ $sum_b }}</td>
                            <td>{{ $sum_r }}</td>
                            <td>{{ $sum_m }}</td>
                            <td>{{ $sum_mm }}</td>
                            <td>{{ $suma_total }}</td>
                            <td>{{ round($porc_mb, 2) }}%</td>
                            <td>{{ round($porc_b, 2) }}%</td>
                            <td>{{ round($porc_r, 2) }}%</td>
                            <td>{{ round($porc_m, 2) }}%</td>
                            <td>{{ round($porc_mm, 2) }}%</td>
                            <td>{{ round($porc_t2b, 2) }}%</td>
                            <td>{{ $sum_porc }}%</td>

                            <?php //Log::info('Preg: '.$preg_id.' || sum_porc: '.$sum_porc); ?>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endforeach
@else
    <?php //Log::info('Encuesta sin data '); ?>
    <table>
        <thead>
            <tr>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>
@endif