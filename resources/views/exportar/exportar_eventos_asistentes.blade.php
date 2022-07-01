<table>
    <thead>
        <tr>
            <th style="width: 40px;background:#0971c8; color:#FFFFFF;font-weight:bold;">EVENTO</th>
            <th style="width: 18px;background:#0971c8; color:#FFFFFF;font-weight:bold;">FECHA</th>
            <th style="width: 14px;background:#0971c8; color:#FFFFFF;font-weight:bold;">DNI</th>
            <th style="width: 25px;background:#0971c8; color:#FFFFFF;font-weight:bold;">NOMBRE</th>
            <th style="width: 25px;background:#0971c8; color:#FFFFFF;font-weight:bold;">MODULO</th>
            <th style="width: 25px;background:#0971c8; color:#FFFFFF;font-weight:bold;">CARRERA</th>
            <th style="width: 14px;background:#0971c8; color:#FFFFFF;font-weight:bold;">CICLO</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">GRUPO</th>
            <th style="width: 21px;background:#0971c8; color:#FFFFFF;font-weight:bold;">ASISTENCIA 1</th>
            <th style="width: 21px;background:#0971c8; color:#FFFFFF;font-weight:bold;">ASISTENCIA 2</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">CANT. ENTRADAS</th>
            <th style="width: 17px;background:#0971c8; color:#FFFFFF;font-weight:bold;">CANT. SALIDAS</th>
        </tr>
    </thead>
    <tbody>
    @foreach($data as $evento)
        @foreach ($evento->invitados as $invitado)
            <tr>
                <td>{{$evento->titulo}}</td>
                <td>{{Carbon\Carbon::parse($evento->fecha_inicio)->format('d-m-Y')}}</td>
                <td>{{$invitado->asistente_data->dni}}</td>
                <td>{{$invitado->asistente_data->nombre}}</td>
                <td>{{$invitado->asistente_data->config->etapa}}</td>
                <td>{{$invitado->asistente_data->matricula_presente->carrera->nombre}}</td>
                <td>{{$invitado->asistente_data->matricula_presente->ciclo->nombre}}</td>
                <td>{{$invitado->asistente_data->criterio->valor}}</td>
                <td>{{$invitado->primera_asistencia ?? 'No Presente'}}</td>
                <td>{{$invitado->segunda_asistencia ?? 'No Presente'}}</td>
                @php
                    $actividad_invitado = $evento->actividad_invitados->where('usuario_id', $invitado->usuario_id)->first();
                @endphp
                <td>{{ $actividad_invitado ? $actividad_invitado->cant_ingresos : 0}}</td>
                <td>{{ $actividad_invitado ? $actividad_invitado->cant_salidas : 0}}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
