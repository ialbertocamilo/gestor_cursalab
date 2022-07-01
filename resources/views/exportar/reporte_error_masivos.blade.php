<table>
    <tr></tr>
    <tr></tr>
    <tr>
        <td></td>
        @foreach ($headers as $header)
            <td style="text-align:center;background-color: #ffc000;width:20px">{{$header['text']}}</td>
        @endforeach
    </tr>
    @foreach ($errores as $err)
        <tr>
            @foreach ($err->err_data_original as $data)
                <td>{{$data}}</td>
            @endforeach
        </tr>
    @endforeach
</table>