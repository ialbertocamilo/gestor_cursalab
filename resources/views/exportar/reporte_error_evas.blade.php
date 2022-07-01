<table>
    <tbody>
    <tr></tr>
    <tr>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Módulo</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Nombre del Curso</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Tema</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Pregunta</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Alternativa 1</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Alternativa 2</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Alternativa 3</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Alternativa 4</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Alternativa 5</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Alternativa 6</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Alternativa 7</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Respuesta Correcta</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Estado</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Error</strong></td>
        </tr>
    </tbody>
    @if(count($errores)>0)
        @foreach ($errores as $err)
            @php
                $datos_err = json_decode($err->err_data);
                $err_type = json_decode($err->err_type);
                $err_msg = '';
                foreach ($err_type as $value) {
                    $err_msg= $err_msg.' '.$value->mensajes.' <br>';
                }
            @endphp
            <tr>
                @foreach ($datos_err[0] as $dat)
                    <td>{{$dat}}</td>
                @endforeach
                <td>{{$err_msg}}</td>
            </tr>
        @endforeach
    @endif
</table>