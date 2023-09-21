<table>
    <thead>
    <tr>
        <th width="150px" align="center"><b>REUNIÓN</b></th>
        <th width="150px" align="center"><b>MÓDULO</b></th>
        <th width="150px" align="center"><b>ROL</b></th>
        <th width="150px" align="center"><b>DNI</b></th>
        <th width="150px" align="center"><b>NOMBRE</b></th>

        {{-- Criteria names --}}

        @foreach ($criteria as $criterion)
            <th width="150px" align="center"><b>{{ $criterion->name }}</b></th>
        @endforeach

        {{--            <th width="150px" align="center"><b>APELLIDO PATERNO</b></th>--}}
        {{--            <th width="150px" align="center"><b>APELLIDO MATERNO</b></th>--}}
        {{-- <th width="150px" align="center"><b>TOTAL INGRESOS</b></th> --}}
        {{-- <th width="150px" align="center"><b>TOTAL SALIDAS</b></th> --}}
        {{-- <th width="150px" align="center"><b>TOTAL DURACIÓN</b></th> --}}
        {{-- <th width="150px" align="center"><b>PRIMERA ENTRADA</b></th> --}}
        {{-- <th width="150px" align="center"><b>PRIMERA SALIDA</b></th> --}}
        {{-- <th width="150px" align="center"><b>ÚLTIMA ENTRADA</b></th> --}}
        {{-- <th width="150px" align="center"><b>ÚLTIMA SALIDA</b></th> --}}
        <th width="150px" align="center"><b>PRIMERA ASISTENCIA</b></th>
        <th width="150px" align="center"><b>SEGUNDA ASISTENCIA</b></th>
        <th width="150px" align="center"><b>TERCERA ASISTENCIA</b></th>
        <th width="150px" align="center"><b>MINUTOS EN REUNION</b></th>
        <th width="150px" align="center"><b>PRESENCIA EN REUNION</b></th>

        @if ($isAllowedToViewAll)
            <th width="150px" align="center"><b>NAVEGADOR</b></th>
            <th width="180px" align="center"><b>VERSIÓN DE NAVEGADOR</b></th>
            <th width="150px" align="center"><b>PLATAFORMA</b></th>
            <th width="180px" align="center"><b>VERSIÓN DE PLATAFORMA</b></th>
            <th width="150px" align="center"><b>DISPOSITIVO</b></th>
            <th width="180px" align="center"><b>MODELO DE DISPOSITIVO</b></th>
        @endif
    </tr>
    </thead>
    <tbody>
    @foreach ($attendants as $key => $attendant)
        <tr>
            <td align="center">{{ $attendant->meeting->name }}</td>
            <td align="center">{{ $attendant->user->subworkspace->name }}</td>
            <td align="center">{{ $attendant->type ? $attendant->type->name : '' }}</td>
            <td align="center">{{ $attendant->usuario->document }}</td>

            <td>{{ $attendant->usuario->name.' '.$attendant->usuario->lastname.' '.$attendant->usuario->surname  }}</td>

            {{-- User criteria values --}}

            @foreach ($criteria as $criterion)
                <td>
                    {{
                        getCriterionValue(
                            $criterion->id,
                            $attendant->user->criterion_values
                       )
                    }}
                </td>
            @endforeach


            {{--                <td>{{ $attendant->usuario->apellido_paterno }}</td>--}}
            {{--                <td>{{ $attendant->usuario->apellido_materno }}</td>--}}

            {{-- <td>{{ $attendant->total_logins ?? 0}}</td> --}}
            {{-- <td>{{ $attendant->total_logouts ?? 0}}</td> --}}
            {{-- <td>{{ $attendant->total_duration ?? 0}}</td> --}}
            {{-- <td>{{ $attendant->first_login_at ? $attendant->first_login_at->format('d/m/Y H:i') : '-'}}</td> --}}
              {{-- <td>{{ $attendant->first_logout_at ? $attendant->first_logout_at->format('d/m/Y H:i') : '-'}}</td> --}}
            {{-- <td>{{ $attendant->last_login_at ? $attendant->last_login_at->format('d/m/Y H:i') : '-'}}</td> --}}
            {{-- <td>{{ $attendant->last_logout_at ? $attendant->last_logout_at->format('d/m/Y H:i') : '-'}}</td> --}}

            <td align="center">{{ $attendant->present_at_first_call ? 'Presente' :'Ausente' }}</td>
            <td align="center">{{ $attendant->present_at_middle_call ? 'Presente' :'Ausente' }}</td>
            <td align="center">{{ $attendant->present_at_last_call ? 'Presente' :'Ausente' }}</td>
            <td align="center">{{ $attendant->total_duration ?? 0 }}</td>
            <td align="center">{{ $attendant->getTotalDurationPercentInMeeting() }}%</td>


            @if ($isAllowedToViewAll)
            <td align="center">{{ $attendant->browser_family->name ?? 'No identificado' }}</td>
                <td align="center">{{ $attendant->browser_version->name ?? 'No identificado' }}</td>
                <td align="center">{{ $attendant->platform_family->name ?? 'No identificado' }}</td>
                <td align="center">{{ $attendant->platform_version->name ?? 'No identificado' }}</td>
                <td align="center">{{ $attendant->device_family->name ?? 'No identificado' }}</td>
                <td align="center">{{ $attendant->device_model->name ?? 'No identificado' }}</td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
