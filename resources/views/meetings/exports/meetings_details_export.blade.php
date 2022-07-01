<table>
    <tbody>
    @for($i = 0; $i < 7; $i++)
        <tr></tr>
    @endfor

    <tr>
        <td></td>
        <th align="center" colspan="3"><b>{{ $meeting->name }}</b></th>
    </tr>

    <tr></tr>

    <tr>
        <td></td>
        <th width="260px"><b>FECHA DE INICIO PROGRAMADO</b></th>
        <td width="280px" align="center">{{$meeting->starts_at->format("d/m/Y g:i a")}}</td>
    </tr>
    <tr>
        <td></td>
        <th width="260px"><b>FECHA DE FIN PROGRAMADO</b></th>
        <td width="280px" align="center">{{$meeting->finishes_at->format("d/m/Y g:i a")}}</td>
    </tr>
    <tr>
        <td></td>
        <th width="260px"><b>DURACIÓN</b></th>
        <td width="280px" align="center">{{$meeting->duration}} minutos</td>
    </tr>
    <tr>
        <td></td>
        <th width="260px"><b>TOMA DE ASISTENCIAS</b></th>
        <td width="280px" align="center">
            {{ $meeting->attendance_call_first_at ? $meeting->attendance_call_first_at->format('g:i:s A') : 'No definido' }}
            -
            {{ $meeting->attendance_call_middle_at ? $meeting->attendance_call_middle_at->format('g:i:s A') : 'No definido' }}
            -
            {{ $meeting->attendance_call_last_at ? $meeting->attendance_call_last_at->format('g:i:s A') : 'No definido' }}
        </td>
    </tr>
    <tr>
        <td></td>
        <th width="260px"><b>FECHA DE INICIO REAL</b></th>
        <td width="280px"
            align="center">{{$meeting->started_at ? $meeting->started_at->format("d/m/Y g:i a") : '-'}}</td>
    </tr>
    <tr>
        <td></td>
        <th width="260px"><b>FECHA DE FIN REAL</b></th>
        <td width="280px"
            align="center">{{$meeting->finished_at ? $meeting->finished_at->format("d/m/Y g:i a") : '-'}}</td>
    </tr>

    @if($meeting->started_at AND $meeting->finished_at)
        <tr>
            <td></td>
            <th width="260px"><b>DURACIÓN REAL</b></th>
            <td width="280px" align="center">{{ $meeting->getTotalDuration() }} minutos</td>
        </tr>
    @endif

    @if($meeting->user)
        <tr>
            <td></td>
            <th width="260px" style="vertical-align: center"><b>CREADOR</b></th>
            <td width="280px" align="center">
                {{$meeting->user->name}} <br>
                {{$meeting->user->email}}
            </td>
        </tr>
    @endif
    <tr>
        <td></td>
        <th width="260px" style="vertical-align: center"><b>ANFITRIÓN</b></th>
        <td width="280px" align="center">
            {{$meeting->host->dni}} - {{$meeting->host->nombre}}
        </td>
    </tr>
    <tr>
        <td></td>
        <th width="260px"><b>TIPO DE REUNIÓN</b></th>
        <td width="280px" align="center">{{$meeting->type->name}}</td>
    </tr>
    <tr>
        <td></td>
        <th width="260px"><b>ESTADO</b></th>
        <td width="280px" align="center">{{$meeting->status->name}}</td>
    </tr>
    <tr>
        <td></td>
        <th width="260px"><b>CANTIDAD DE INVITADOS</b></th>
        <td width="280px" align="center">{{$meeting->attendants->count()}}</td>
    </tr>

    <tr>
        <td></td>
        <th width="260px"><b>CANTIDAD DE ASISTENTES</b></th>
        <td width="280px" align="center">
            {{$meeting->attendantsWithFirstLogintAt()->count()}}
            ({{ $meeting->getRealPercetageOfAttendees() }}%)
        </td>
    </tr>

    <tr>
        <td></td>
        <th width="260px"><b>FECHA DE CREACIÓN</b></th>
        <td width="280px" align="center">{{$meeting->created_at->format('d/m/Y g:i:s a')}}</td>
    </tr>

    @if($meeting->report_generated_at)

        <tr>
            <td></td>
            <th width="260px"><b>ÚLTIMA FECHA DE REPORTE</b></th>
            <td width="280px" align="center">{{$meeting->report_generated_at->format('d/m/Y g:i:s a')}}</td>
        </tr>

    @endif
    </tbody>
</table>
