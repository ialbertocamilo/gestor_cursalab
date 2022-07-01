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
</style>

<?php 
$curso_id = $data['curso_id'];
$idg = $data['idg'];
$enc_id = $data['enc_id'];
$mod = $data['mod'];
$pgtas = $data['pgtas'];
//
$cab = array();
$btable = "";
//
$criterio_valor_x_rpta = array();
?>
<table>
  <thead>
    <tr>
      <th>Pregunta</th>
      <th>Respuestas</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    foreach ($pgtas as $item){
      if ($item->tipo_pregunta == 'simple'){
        $mainpreg = $item->titulo;
        $rptas = HomeController::respuestasEncuesta($item->id, $mod, $curso_id, $idg );
        $cont_rptas = 0;
        foreach ($rptas as $rpta) {
          $valores = json_decode($rpta, true);
          ?>
          <tr>
            <td>{{ $mainpreg }}</td>
            <td>{{ $valores['respuestas'] }}</td>
          </tr>
          <?php 
        }
      }
    }
    ?>
  </tbody>
</table>
