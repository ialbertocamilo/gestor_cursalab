<table>
    <tbody>
    <tr></tr>
    <tr>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Módulo</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Escuela</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Nombre del Curso</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Nombre del Tema</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Requisito Tema</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Descripción Tema</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Evaluable</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Estado</strong></td>
        <td style="text-align:center;background-color: #ffc000;width:20px"><strong>Orden</strong></td>
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
                    $err_msg= $err_msg.' '.$value->mensajes.' - ';
                }
            @endphp
            <tr>
                @for ($i = 0; $i < count($datos_err[0]); $i++)
                        <td>{{$datos_err[0][$i]}}</td>
                        @if($i==8)
                            <td>{{$err_msg}}</td>
                        @endif
                @endfor
            </tr>
        @endforeach
    @endif
</table>