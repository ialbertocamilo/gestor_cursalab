<table>
    <thead>
        <tr>
            <th width="80px" align="center"><b>ID CURSO</b></th>
            <th width="300px" align="center"><b>CURSO</b></th>
            <th width="80px" align="center"><b>ESTADO</b></th>
            {{-- <th width="250px" align="center"><b>SEGMENTO</b></th> --}}
            <th width="200px" align="center"><b>TIPO DE SEGMENTACIÓN</b></th>
            <th width="100px" align="center"><b>SEGMENTO</b></th>
            <th width="150px" align="center"><b>CRITERIO</b></th>
            <th width="200px" align="center"><b>VALOR</b></th>
        </tr>
    </thead>

    <tbody>
        @foreach($courses as $course)

            @php
                $i = 0;
            @endphp

            @foreach($course->segments as $segment)

                @php
                    $i++;
                @endphp
                
                @foreach($segment->values as $segment_value)

                    <tr>
                        <td align="center">{{ $course->id }}</td>
                        <td align="left">{{ $course->name }}</td>
                        <td align="center">{{ $course->active ? 'Activo' : 'Inactivo' }}</td>
                        {{-- <td align="left">{{ $segment->name }}</td> --}}
                        <td align="left">{{ $segment->type->name }}</td>
                        <td align="center">Segmento {{ $i }}</td>
                        <td align="center">{{ $segment_value->criterion->name }}</td>
                        {{-- <td align="center">{{ $segment_value->criterion->field_type->name }}</td> --}}

                        <td align="left">{{ $segment_value->getCriterionValueText() }}</td>
                    </tr>

                @endforeach

            @endforeach

        @endforeach
    </tbody>
</table>
