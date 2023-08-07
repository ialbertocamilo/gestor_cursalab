<div>
    <div>
        <tr>
            <th width="30" style="font-weight: bold;"> CAMPAÑA: {{ $campaign->title }}  </th>
            @if($start_date && $end_date)
                <th width="30" style="font-weight: bold;"> FECHA DE INICIO: {{ $start_date  }} </th>
                <th width="30" style="font-weight: bold;"> FECHA DE FIN: {{ $end_date }} </th>
            @endif
        </tr>
    </div>
    <br>
    <table class="table table-hover ec_tabla tabla-t1" id="table_resumen">
        <thead class="bg-dark text-center">
            <tr>
                <th width="50" style="text-align: center; font-weight: bold;">CANDIDATO - APELLIDOS Y NOMBRES</th>
                <th width="30" style="text-align: center; font-weight: bold;">CANDIDATO - NRO DOCUMENTO</th>
                <th width="50" style="text-align: center; font-weight: bold;">CANDIDATO - CRITERIO</th>
                <th width="30" style="text-align: center; font-weight: bold;">CANDIDATO - VOTOS</th>
                <th width="50" style="text-align: center; font-weight: bold;">VOTANTE - APELLIDOS Y NOMBRES</th>
                <th width="30" style="text-align: center; font-weight: bold;">VOTANTE - NRO DOCUMENTO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($candidates as $item)
                @if (count($item->votations) === 0)
                <tr>
                    <td align="center">{{ $item->user->lastname }} {{ $item->user->surname }}, {{ $item->user->name }} </td>
                    <td align="center">{{ $item->user->document }}</td>
                    <td align="center">{{ $item->getCriterioValueUserCampaign() }}</td>
                    <td align="center">{{ $item->votes }}</td>
                    
                    <td align="center">VACIO</td>
                    <td align="center">VACIO</td>
                </tr>
                @else
                    @foreach ($item->votations as $subitem)
                    <tr>
                        <td align="center">{{ $item->user->lastname }} {{ $item->user->surname }}, {{ $item->user->name }} </td>
                        <td align="center">{{ $item->user->document }}</td>
                        <td align="center">{{ $item->getCriterioValueUserCampaign() }}</td>
                        <td align="center">{{ $item->votes }}</td>

                        <td align="center"> {{ $subitem->user->lastname ?? 'ADMIN' }} 
                                            {{ $subitem->user->surname ?? 'ADMIN' }}, 
                                            {{ $subitem->user->name ?? 'ADMIN' }} </td>
                        <td align="center"> {{ $subitem->user->document ?? 'ADMIN' }}</td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
        
        </tbody>
    </table>
</div>
