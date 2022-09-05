<table>
    <thead>
        <tr>
            <td style="width:100%;center;background-color: #5eff00;width:30px">NOMBRES</td>
            <td style="width:100%;center;background-color: #5eff00;width:30px">APELLIDO PATERNO</td>
            <td style="width:100%;center;background-color: #5eff00;width:30px">APELLIDO MATERNO</td>
            <td style="width:100%;center;background-color: #5eff00;width:30px">IDENTIFICADOR</td>
            <td style="width:100%;center;background-color: #5eff00;width:30px">EMAIL</td>
            @foreach ($criteria as $criterion)
                <td style="text-align:center;background-color: #ffc000;width:30px">{{$criterion->name}}</td>
            @endforeach
        </tr>
    </thead>
</table>
