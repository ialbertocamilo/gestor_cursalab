<?php 
  use App\Http\Controllers\HomeController;
?>
<style>
table {
  border-collapse: collapse;
}
th,
td {
  border: 1px solid #cecfd5;
  padding: 5px;
}
th{text-align: center;}
.xl10422630{    
    color:white;
    font-size:12.0pt;
    font-weight:700;    
    text-align:center;
    border-top:.5pt solid black;
    border-right:.5pt solid black;
    border-bottom:none;
    border-left:.5pt solid black;
    background:#00B050;
    mso-pattern:#00B050 none;
    white-space:normal;
}
.xl10522630{    
    color:white;
    font-size:11.0pt;
    font-weight:700;    
    text-align:center;
    border-top:.5pt solid black;
    border-right:.5pt solid black;
    border-bottom:none;
    border-left:.5pt solid black;
    background:#00B050;
    mso-pattern:#00B050 none;
    white-space:normal;
}
.xl8222630{
    color:white;
    font-size:8.0pt;
    font-weight:700;    
    text-align:center;
    border:.5pt solid black;
    background:#C00000;
    mso-pattern:#C00000 none;
    white-space:nowrap;
}
.xl8322630{
    color:black;
    font-size:8.0pt;
    font-weight:400;    
    text-align:center;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:nowrap;
}
.xl6622630{
    color:navy;
    font-size:10.0pt;
    font-weight:400;    
    text-align:general;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:nowrap;
}
.xl10622630{
    color:white;
    font-size:10.0pt;
    font-weight:700;    
    text-align:center;
    border-top:.5pt solid black;
    border-right:none;
    border-bottom:.5pt solid black;
    border-left:.5pt solid black;
    background:#00B050;
    mso-pattern:#00B050 none;
    white-space:nowrap;
}
.xl10222630{
    color:white;
    font-size:10.0pt;
    font-weight:700;    
    text-align:center;
    border-top:.5pt solid black;
    border-right:.5pt solid black;
    border-bottom:none;
    border-left:.5pt solid black;
    background:#00B050;
    mso-pattern:#00B050 none;
    white-space:normal;
}
.xl8522630{
    color:black;
    font-size:8.0pt;
    font-weight:700;    
    text-align:center;
    border:.5pt solid black;
    background:yellow;
    mso-pattern:yellow none;
    white-space:nowrap;
}
.xl8422630{
    color:white;
    font-size:8.0pt;
    font-weight:700;    
    text-align:center;
    border:.5pt solid black;
    background:#00B050;
    mso-pattern:#00B050 none;
    white-space:nowrap;
}
.xl8622630{
    color:black;
    font-size:11.0pt;
    font-weight:400;    
    text-align:general;
    border:.5pt solid black;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:normal;
}
.xl8722630
    {
    color:black;
    font-size:11.0pt;
    font-weight:400;    
    text-align:center;
    border:.5pt solid black;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:normal;
}
.xl8822630{
    color:black;
    font-size:11.0pt;
    font-weight:400;    
    text-align:center;
    border:.5pt solid black;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:nowrap;
}
.xl8922630{
    color:black;
    font-size:8.0pt;
    font-weight:400;    
    text-align:center;
    border:.5pt solid black;
    background:yellow;
    mso-pattern:yellow none;
    white-space:nowrap;
}
.xl9022630{
    color:black;
    font-size:8.0pt;
    font-weight:400;    
    text-align:center;
    border:.5pt solid black;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:nowrap;
}
.xl10422630, .xl10522630, .xl8222630, .xl8322630, .xl6622630, 
.xl10622630, .xl10222630, .xl8522630, .xl8422630, .xl8622630, 
.xl8722630, .xl8822630, .xl8922630, .xl9022630{
    vertical-align:middle;
    font-style:normal;
    text-decoration:none;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    mso-number-format:General;
}
.xl9122630{
    color:black;
    font-size:8.0pt;
    font-weight:400;
    font-style:normal;
    text-decoration:none;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    mso-number-format:0%;
    text-align:center;
    vertical-align:middle;
    border-top:.5pt solid black;
    border-right:.5pt solid black;
    border-bottom:.5pt solid black;
    border-left:none;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:nowrap;
}
.xl9222630{
    color:black;
    font-size:8.0pt;
    font-weight:400;
    font-style:normal;
    text-decoration:none;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    mso-number-format:0%;
    text-align:center;
    vertical-align:middle;
    border:.5pt solid black;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:nowrap;
}
.xl9322630{
    color:navy;
    font-size:10.0pt;
    font-weight:400;
    font-style:normal;
    text-decoration:none;
    font-family:Calibri;
    mso-generic-font-family:auto;
    mso-font-charset:0;
    mso-number-format:0%;
    text-align:general;
    vertical-align:middle;
    border:.5pt solid black;
    mso-background-source:auto;
    mso-pattern:auto;
    white-space:nowrap;
}
.xl10422630, .xl10522630, .xl8222630, .xl8322630, .xl6622630, 
.xl10622630, .xl10222630, .xl8522630, .xl8422630, .xl8622630, 
.xl8722630, .xl8822630, .xl8922630, .xl9022630, .xl9122630,
.xl9222630, .xl9322630{
    padding-top:5px;
    padding-right:5px;
    padding-left:5px;
    mso-ignore:padding;
}
</style>

<?php 
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
    // print_r($item);

    $data = HomeController::resumenCalifica($item->pregunta_id, $mod, $item->curso_id, $idg);

    if(isset($data) && isset($data['data_rptas'])){
        $data_pygs[$item->curso_id][$item->pregunta_id] = array('titulo' => $item->pregunta_id, 'data_rptas' => $data['data_rptas'] );
        $criterio_valor_x_rpta[$item->curso_id][$item->pregunta_id] = $data['criterio_valor_x_rpta'];
        $tot_rptas = $data['tot_rptas'];
    }

}
$cont_rptas = $tot_rptas;

//dd($data_pygs);
// dd($criterio_valor_x_rpta);

?>
<div style="overflow-x: scroll;">


@if (isset($data_pygs))
    @foreach($data_pygs as $cur_id => $preg_data)

        <table>
            
         <tr height="20" style='height:15.0pt'>
          <td rowspan="2" height="54" class=xl10422630 style='border-bottom:.5pt solid black;height:40.5pt;width:157pt'><strong>Escuela</strong><span style='mso-spacerun:yes'> </span></td>
          <td rowspan="2" height="54" class=xl10422630 style='border-bottom:.5pt solid black;height:40.5pt;width:157pt'><strong>Curso</strong><span style='mso-spacerun:yes'> </span></td>
          <td rowspan="2" height="54" class=xl10422630 style='border-bottom:.5pt solid black;height:40.5pt;width:220pt'><strong>Pregunta</strong><span style='mso-spacerun:yes'> </span></td>

          <td rowspan="2" class="xl10222630" width="84" style='border-bottom:.5pt solid black;width:63pt'><strong>Promedio</strong></td>
          <td rowspan="2" class="xl10222630" width="34" style='border-bottom:.5pt solid black;width:25pt'><strong>Top</strong></td>
          <td rowspan="2" class="xl10222630" width="55" style='border-bottom:.5pt solid black;width:41pt'><strong>Bottom</strong></td>
          <td class="xl8222630" style='border-left:none'>5</td>
          <td class="xl8222630" style='border-left:none'>4</td>
          <td class="xl8222630" style='border-left:none'>3</td>
          <td class="xl8222630" style='border-left:none'>2</td>
          <td class="xl8222630" style='border-left:none'>1</td>
          <td class="xl8322630"></td>
          <td class="xl8322630"></td>
          <td class="xl8322630"></td>
          <td class="xl8322630"></td>
          <td class="xl8322630"></td>
          <td class="xl8322630"></td>
          <td class="xl8322630"></td>
          <td class="xl6622630"></td>
         </tr>

         <tr height="34" style='mso-height-source:userset;height:25.5pt'>
          <td class="xl8522630" style='border-top:none;border-left:none'><strong>Muy Bueno</strong></td>
          <td class="xl8522630" style='border-top:none;border-left:none'><strong>Bueno</strong></td>
          <td class="xl8522630" style='border-top:none;border-left:none'><strong>Regular</strong></td>
          <td class="xl8522630" style='border-top:none;border-left:none'><strong>Malo</strong></td>
          <td class="xl8522630" style='border-top:none;border-left:none'><strong>Muy Malo</strong></td>
          <td class="xl8222630" style='border-left:none'><strong>TOTAL</strong></td>
          <td class="xl8522630" style='border-left:none'><strong>Muy Bueno</strong></td>
          <td class="xl8522630" style='border-left:none'><strong>Bueno</strong></td>
          <td class="xl8522630" style='border-left:none'><strong>Regular</strong></td>
          <td class="xl8522630" style='border-left:none'><strong>Malo</strong></td>
          <td class="xl8522630" style='border-left:none'><strong>Muy Malo</strong></td>
          <td class="xl8522630" style='border-left:none'><strong>T2B</strong></td>
          <td class="xl6622630"></td>
         </tr> 

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
                <tr height="20" style='height:15.0pt'>
                    <td height="20" class="xl8622630" width="209" style='height:15.0pt;border-top:none;width:157pt'>{{ isset($categorias_arr[$cur_id]) ? $categorias_arr[$cur_id] : "" }}</td>
                    <td height="20" class="xl8622630" width="209" style='height:15.0pt;border-top:none;width:157pt'>{{ isset($cursos_arr[$cur_id]) ? $cursos_arr[$cur_id] : "" }}</td>
                    <td height="20" class="xl8622630" width="209" style='height:15.0pt;border-top:none;width:157pt'>{{ isset($preguntas_arr[$preg_id]) ? $preguntas_arr[$preg_id] : "" }}</td>
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
                                    // echo $valor."<br>";
                                } 
                                $prom = round(($suma_valores/$contador), 2);
                            }
                            // Log::info('Preg: '.$preg_id.' || Prom: '.$prom);
                            // dd($data_pygs);
                            ?>
                        <td class="xl8822630" style='border-top:none;border-left:none'>{{ $prom }}</td>
                        <td class="xl8822630" style='border-top:none;border-left:none'>{{ $top }}</td>
                        <td class="xl8822630" style='border-top:none;border-left:none'>{{ $bottom }}</td>
                        <td class="xl8922630" style='border-top:none;border-left:none'>{{ $sum_mb }}</td>
                        <td class="xl8922630" style='border-top:none;border-left:none'>{{ $sum_b }}</td>
                        <td class="xl8922630" style='border-top:none;border-left:none'>{{ $sum_r }}</td>
                        <td class="xl8922630" style='border-top:none;border-left:none'>{{ $sum_m }}</td>
                        <td class="xl8922630" style='border-top:none;border-left:none'>{{ $sum_mm }}</td>
                        <td class="xl9022630" style='border-top:none;border-left:none'>{{ $suma_total }}</td>
                        <td class="xl9122630" style='border-top:none'>{{ round($porc_mb, 2) }}%</td>
                        <td class="xl9222630" style='border-top:none;border-left:none'>{{ round($porc_b, 2) }}%</td>
                        <td class="xl9222630" style='border-top:none;border-left:none'>{{ round($porc_r, 2) }}%</td>
                        <td class="xl9222630" style='border-top:none;border-left:none'>{{ round($porc_m, 2) }}%</td>
                        <td class="xl9222630" style='border-top:none;border-left:none'>{{ round($porc_mm, 2) }}%</td>
                        <td class="xl9222630" style='border-top:none;border-left:none'>{{ round($porc_t2b, 2) }}%</td>
                        <td class="xl9322630" align='right' style='border-left:none'>{{ $sum_porc }}%</td>

                        <?php Log::info('Preg: '.$preg_id.' || sum_porc: '.$sum_porc); ?>
                </tr>
            @endforeach
        @endforeach
        </table>

        <table >
            <tr><td style="border:none;">&nbsp;</td></tr>
        </table>
        
    @endforeach
@endif
</div>
