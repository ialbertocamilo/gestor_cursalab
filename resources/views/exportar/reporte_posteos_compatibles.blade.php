<table>
    <tbody>
    <tr>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Modulo</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Escuela</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Curso</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Tema</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Modulo</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Escuela</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Curso</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Tema</strong></td>
    </tr>
    </tbody>
    @foreach ($p_compas as $p_compa)
        <tr>
            <td>{{$p_compa->posteo->curso->config->etapa ?? '-'}}</td>
            <td>{{$p_compa->posteo->curso->categoria->nombre ?? '-'}}</td>
            <td>{{$p_compa->posteo->curso->nombre ?? '-'}}</td>

            <td>{{$p_compa->posteo->nombre ?? '-'}}</td>

            <td>{{$p_compa->posteo_compa->curso->config->etapa ?? '-'}}</td>
            <td>{{$p_compa->posteo_compa->curso->categoria->nombre ?? '-'}}</td>
            <td>{{$p_compa->posteo_compa->curso->nombre ?? '-'}}</td>

            <td>{{$p_compa->posteo_compa->nombre ?? '-'}}</td>

            {{--        @if($p_compa->posteo_compa->curso)--}}
            {{--                <td>{{$p_compa->posteo->curso->config->etapa}}</td>--}}
            {{--                <td>{{$p_compa->posteo->curso->categoria->nombre}}</td>--}}
            {{--                <td>{{$p_compa->posteo->curso->nombre}}</td>--}}
            {{--            @else--}}
            {{--                <td>-</td>--}}
            {{--                <td>-</td>--}}
            {{--                <td>-</td>--}}
            {{--            @endif--}}
            {{--            <td>{{$p_compa->posteo->nombre}}</td>--}}
            {{--            @if($p_compa->posteo_compa->curso)--}}
            {{--                <td>{{$p_compa->posteo_compa->curso->config->etapa}}</td>--}}
            {{--                <td>{{$p_compa->posteo_compa->curso->categoria->nombre}}</td>--}}
            {{--                <td>{{$p_compa->posteo_compa->curso->nombre}}</td>--}}
            {{--            @else--}}
            {{--                <td>-</td>--}}
            {{--                <td>-</td>--}}
            {{--                <td>-</td>--}}
            {{--            @endif--}}
            {{--            <td>{{$p_compa->posteo_compa->nombre}}</td>--}}
        </tr>
    @endforeach
</table>
