<table>
    <thead>
      <tr>
        <td style="background-color: #000000; color: #FFFFFF;">Curso_nombre</td>
        <td style="background-color: #000000; color: #FFFFFF;">Tema Nuevos</td>
        <td style="background-color: #000000; color: #FFFFFF;">Curso_nombre</td>
        <td style="background-color: #000000; color: #FFFFFF;">Temas Viejos</td>
      </tr>
    </thead>
    @for ($i = 0; $i < $array_mayor; $i++)
      <tr>
        @if(isset($temas_n_r[$i]['nombre']))
          <td >{{ ($temas_n_r[$i]['curso']["nombre"]) ? $temas_n_r[$i]['curso']["nombre"] : '' }}</td>
          <td @isset($temas_n_r[$i]['color']) style="background:{{$temas_n_r[$i]['color']}} " @endisset>{{ $temas_n_r[$i]['nombre'] }}</td>
        @else
          <td></td>
          <td></td>
        @endif
        @isset($temas_o_r[$i]->nombre)
          <td >{{ ($temas_o_r[$i]->curso_nombre) ? $temas_o_r[$i]->curso_nombre : '' }}</td>
          <td @isset($temas_o_r[$i]->color) style="background:{{$temas_o_r[$i]->color}} " @endisset>{{$temas_o_r[$i]->nombre}}</td>
        @endisset
      </tr>
    @endfor
</table>