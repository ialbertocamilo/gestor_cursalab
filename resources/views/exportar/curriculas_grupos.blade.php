<table>
    <thead>
    <tr>
        <th style="width: 40px;background:#0971c8; color:#FFFFFF;font-weight:bold;">Modulo</th>
        <th style="width: 45px;background:#0971c8; color:#FFFFFF;font-weight:bold;">Escuela</th>
        <th style="width: 70px;background:#0971c8; color:#FFFFFF;font-weight:bold;">Curso</th>
        <th style="width: 40px;background:#0971c8; color:#FFFFFF;font-weight:bold;">Carrera</th>
        <th style="width: 20px;background:#0971c8; color:#FFFFFF;font-weight:bold;">Ciclo</th>
        <th style="width: 20px;background:#0971c8; color:#FFFFFF;font-weight:bold;">Grupo</th>
    </tr>
    </thead>
    <tbody>
    @foreach($modulos as $modulo)
        <tr>
            <td>{{$modulo->etapa}}</td>
        </tr>
        @foreach ($modulo->categorias as $escuela)
        <tr>
            <td>{{$modulo->etapa}}</td>
            <td>{{$escuela->nom_categoria}}</td>
        </tr>
            @foreach ($escuela->cursos as $curso)
            <tr>
                <td>{{$modulo->etapa}}</td>
                <td>{{$escuela->nom_categoria}}</td>
                <td>{{$curso->nom_curso}} </td>
            </tr>
                @foreach ($curso->curriculas as $curricula)
                    <tr>
                        <td>{{$modulo->etapa}}</td>
                        <td>{{$escuela->nom_categoria}}</td>
                        <td>{{$curso->nom_curso}}</td>
                        <td>{{$curricula->nombre_carrera}}</td>
                        <td>{{$curricula->nombre_ciclo}}</td>
                        <td>{{$curricula->nombre_criterio}}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    @endforeach
    </tbody>
</table>
