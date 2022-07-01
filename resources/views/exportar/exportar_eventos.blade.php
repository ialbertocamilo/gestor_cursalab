<table>
    <thead>
        <tr>
            <th style="width: 45px;background:#0971c8; color:#FFFFFF;font-weight:bold;">EVENTO</th>
            <th style="width: 70px;background:#0971c8; color:#FFFFFF;font-weight:bold;">DESCRIPCION</th>
            <th style="width: 18px;background:#0971c8; color:#FFFFFF;font-weight:bold;">ESTADO</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">FECHA</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">HR. INICIO</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">HR. FIN</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">HR. INICIO REAL</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">HR. FIN REAL</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">DURACION</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">CANT. INVITADOS</th>
        </tr>
    </thead>
    <tbody>
    @foreach($data as $evento)
        <tr>
            <td>{{$evento->titulo}}</td>
            <td>{{(is_null($evento->descripcion) || empty($evento->descripcion) || $evento->descripcion == 'null' ) ? '' : $evento->descripcion}}</td>
            <td>{{$evento->estado->estado}}</td>
            <td>{{Carbon\Carbon::parse($evento->fecha_inicio)->format('d-m-Y')}}</td>
            <td>{{Carbon\Carbon::parse($evento->fecha_inicio)->format('H:i')}}</td>
            <td>{{Carbon\Carbon::parse($evento->fecha_fin)->format('H:i')}}</td>
            <td>{{Carbon\Carbon::parse($evento->hora_inicio_real)->format('H:i')}}</td>
            <td>{{Carbon\Carbon::parse($evento->hora_fin_real)->format('H:i')}}</td>
            <td>{{$evento->duracion}}</td>
            <td>{{$evento->invitados_count}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
