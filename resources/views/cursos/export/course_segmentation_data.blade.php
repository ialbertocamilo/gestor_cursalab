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
    @php
        $i = 1;
        $currentSegmentId = 0;
        $currentCourseId = 0;
    @endphp
    @foreach($courses as $course)

        @php
            if ($course->segment_id != $currentSegmentId && $currentCourseId != $course->id) :
                $currentSegmentId = $course->segment_id;
                $currentCourseId = $course->id;
                $i = 1;
            endif;

            if ($course->segment_id != $currentSegmentId && $currentCourseId == $course->id) :
                $currentSegmentId = $course->segment_id;
                $i++;
            endif;

        @endphp
        <tr>
            <td align="center">{{ $course->id }}</td>
            <td align="left">{{ $course->name }}</td>
            <td align="center">{{ $course->active ? 'Activo' : 'Inactivo' }}</td>
            <td align="left">{{ $course->segmentation_type }}</td>
            <td align="center">Segmento {{ $i }}</td>
            <td align="center">{{ $course->criteria_name }}</td>
            <td align="left">{{ $course->value_text }}</td>
        </tr>

    @endforeach
    </tbody>
</table>
