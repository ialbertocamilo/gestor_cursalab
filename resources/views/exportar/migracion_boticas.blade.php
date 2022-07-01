<table>
    <thead>
        <tr>
            <th style="width: 45px; font-weight:bold;" align="center">Modulo</th>
            <th style="width: 70px; font-weight:bold;" align="center">DNI</th>
            <th style="width: 70px; font-weight:bold;" align="center">Nombres</th>
            <th style="width: 70px; font-weight:bold;" align="center">Botica</th>
            <th style="width: 70px; font-weight:bold;" align="center">Grupo</th>
            <th style="width: 70px; font-weight:bold;" align="center">Cargo</th>
            <th style="width: 70px; font-weight:bold;" align="center">Género</th>
            <th style="width: 70px; font-weight:bold;" align="center">Estado</th>
            <th style="width: 70px; font-weight:bold;" align="center">Tipo error</th>
           
        </tr>
    </thead>
    <tbody>
    @foreach($usuarios1 as $us)
        <tr>
            <td>{{ $us->config->etapa }}</td>
            <td>{{ $us->dni }}</td>
            <td>{{ $us->nombre }}</td>
            <td>{{ $us->botica }}</td>
            <td>{{ $us->grupo_nombre }}</td>
            <td>{{ $us->cargo }}</td>
            <td>{{ $us->sexo }}</td>
            <td>{{ ($us->estado == 1) ? 'Activo' : 'Inactivo' }}</td>
            <td>no coincide botica grupo</td>
        </tr>
    @endforeach
    @foreach($usuarios2 as $us)
        <tr>
            <td>{{ $us->config->etapa }}</td>
            <td>{{ $us->dni }}</td>
            <td>{{ $us->nombre }}</td>
            <td>{{ $us->botica }}</td>
            <td>{{ $us->grupo_nombre }}</td>
            <td>{{ $us->cargo }}</td>
            <td>{{ $us->sexo }}</td>
            <td>{{ ($us->estado == 1) ? 'Activo' : 'Inactivo' }}</td>
            <td>no existe en tabla botica</td>
        </tr>
    @endforeach
    </tbody>
    @php
        dd("Entra");
    @endphp
</table>
