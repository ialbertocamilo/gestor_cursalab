<table>
    <tr>
        <td style="text-align:center;background-color: #ffc000;width:30px">USUARIO</td>
        <td style="text-align:center;background-color: #ffc000;width:30px">ACCIÓN REALIZADA</td>
        <td style="text-align:center;background-color: #ffc000;width:30px">SECCIÓN</td>
        <td style="text-align:center;background-color: #ffc000;width:30px">REGISTRO</td>
        <td style="text-align:center;background-color: #ffc000;width:30px">FECHA</td>
        <td style="text-align:center;background-color: #ffc000;width:30px">HORA</td>
        <td style="text-align:center;background-color: #ffc000;width:30px">IP</td>
        <td style="text-align:center;background-color: #ffc000;width:30px">URL</td>
    </tr>
    @php
        $audit_events = config('constantes.audit_events')
    @endphp
    @foreach ($logs as $log)
        <tr>
            <td style="text-align: center;">
                {{$log['user_type']  === "App\\User" ? $log['user']['name'] : $log['user_rest']['nombre']}}
            </td>
            <td style="text-align: center;">{{$audit_events[$log['event']]}}</td>
            <td style="text-align: center;">{{$log['section']['nombre']}}</td>
            <td style="text-align: center;">{{$log->getRecordableName()}}</td>
            <td style="text-align: center;">{{$log['created_at']->format('Y/m/d')}}</td>
            <td style="text-align: center;">{{$log['created_at']->format('H:i a')}}</td>
            <td style="text-align: center;">{{$log['ip_address']}}</td>
            <td style="text-align: center;">{{$log['url']}}</td>
        </tr>
    @endforeach

</table>
