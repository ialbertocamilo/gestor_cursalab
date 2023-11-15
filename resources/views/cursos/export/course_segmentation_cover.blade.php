<table>
    <tbody>
    @for($i = 0; $i < 7; $i++)
        <tr></tr>
    @endfor

    <tr>
        <td></td>
        <th align="center" colspan="2"><b>REPORTE DE CURSOS SEGMENTADOS</b></th>
    </tr>

    <tr></tr>

    <tr>
        <td></td>
        <th width="260px"><b>Workspace</b></th>
        <td width="280px" align="center">{{ $workspace->name }}</td>
    </tr>

    <tr>
        <td></td>
        <th width="260px"><b>Cursos activos</b></th>
        <td width="280px" align="center">{{ $data['courses']['active_count'] }}</td>
    </tr>

    <tr>
        <td></td>
        <th width="260px"><b>Cursos inactivos</b></th>
        <td width="280px" align="center">{{ $data['courses']['inactive_count'] }}</td>
    </tr>
    
    <tr>
        <td></td>
        <th width="260px"><b>Fecha de descarga</b></th>
        <td width="280px" align="center">{{ now()->format("d/m/Y g:i a")}}</td>
    </tr>

    </tbody>
</table>
