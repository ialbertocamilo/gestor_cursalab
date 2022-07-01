<table>
    <thead>
    <tr>
        <th width="150px" align="center"><b>TITULO</b></th>
        <th width="150px" align="center"><b>F. DE INICIO PROGRAMADO</b></th>
        <th width="150px" align="center"><b>F. DE FIN PROGRAMADO</b></th>
        <th width="150px" align="center"><b>DURACION</b></th>
        <th width="150px" align="center"><b>PRIMERA ASISTENCIA</b></th>
        <th width="150px" align="center"><b>SEGUNDA ASISTENCIA</b></th>
        <th width="150px" align="center"><b>TERCERA ASISTENCIA</b></th>
        <th width="150px" align="center"><b>DURACION REAL</b></th>
        <th width="150px" align="center"><b>F. DE INICIO REAL</b></th>
        <th width="150px" align="center"><b>F. DE FIN REAL</b></th>
        {{--        <th width="150px" align="center"><b>CREADOR</b></th>--}}
        <th width="150px" align="center"><b>ANFITRION</b></th>
        <th width="150px" align="center"><b>TIPO DE REUNION</b></th>
        <th width="150px" align="center"><b>ESTADO</b></th>
        <th width="150px" align="center"><b>CANT. DE INVITADOS</b></th>
        <th width="150px" align="center"><b>CANT. DE ASISTENTES</b></th>
        <th width="150px" align="center"><b>PORCENTAJE DE ASISTENTES</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($meetings as $key => $meeting)
        <tr>
            <td align="center">{{ $meeting->name }}</td>
            <td align="center">{{ $meeting->starts_at->format("d/m/Y g:i a") }}</td>
            <td align="center">{{ $meeting->finishes_at->format("d/m/Y g:i a") }}</td>
            <td align="center">{{ $meeting->duration }} minutos</td>

            <td align="center">{{ $meeting->attendance_call_first_at->format('g:i:s A') }}</td>
            <td align="center">{{ $meeting->attendance_call_middle_at->format('g:i:s A') }}</td>
            <td align="center">{{ $meeting->attendance_call_last_at->format('g:i:s A') }}</td>

            @if($meeting->started_at AND $meeting->finished_at)
                <td align="center">{{ $meeting->started_at->diffInMinutes($meeting->finished_at) }} minutos</td>
            @else
                <td align="center">-</td>
            @endif

            <td align="center">{{ $meeting->started_at ? $meeting->started_at->format("d/m/Y g:i a") : '-' }}</td>
            <td align="center">{{ $meeting->finished_at ? $meeting->finished_at->format("d/m/Y g:i a") : '-' }}</td>

            {{--            <td align="center">{{ $meeting->user->name }} - {{ $meeting->user->email }}</td>--}}
            <td align="center">{{ $meeting->host->dni }} - {{ $meeting->host->nombre }}</td>

            <td align="center">{{ $meeting->type->name }}</td>
            <td align="center">{{ $meeting->status->name }}</td>

            <td align="center">{{ $meeting->attendants_count }}</td>
            <td align="center">{{ $meeting->attendants_with_first_logint_at_count }}</td>
            <td align="center">{{ $meeting->getRealPercetageOfAttendees() }}%</td>

        </tr>
    @endforeach
    </tbody>
</table>
