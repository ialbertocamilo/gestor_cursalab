<div>
    <table class="table table-hover ec_tabla tabla-t1" id="table_resumen">
        <thead class="bg-dark text-center">
            <tr>
                <th width="50" style="font-weight:bold;">Campaña</th>
                <th width="20" style="font-weight:bold;">Fecha Inicio</th>
                <th width="20" style="font-weight:bold;">Fecha Fin</th>
                <th width="50" style="font-weight:bold;">Usuario postulante</th>
                <th width="20" style="font-weight:bold;">Documento postulante</th>
                <th width="50" style="font-weight:bold;">Candidato</th>
                <th width="20" style="font-weight:bold;">Documento candidato</th>
                <th width="100" style="font-weight:bold;">Sustento</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($postulates as $item)
                @foreach ($item->postulates as $subitem)
                    <tr>
                        <td> {{ $campaign->title }} </td>
                        <td> {{ $start_date }} </td>
                        <td> {{ $end_date }} </td>
                        
                        <td> {{ $subitem->user->name }}, {{$subitem->user->lastname}} {{$subitem->surname}} </td>
                        <td> {{ $subitem->user->document }} </td>

                        <td> {{ $item->user->name }},  {{$item->user->lastname}} {{$item->user->surname}} </td>
                        <td> {{ $item->user->document }} </td>
                        <td> {{ $subitem->sustent }} </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
