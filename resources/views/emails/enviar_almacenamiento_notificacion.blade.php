@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB; text-align:start; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <h3 style="color:#5458EA; font-size: 1.5rem;">
                Solicitud de aumento de capacidades.
            </h3>
            <p style="font-weight: bold;">Administrador: {{ $data['user_admin'] }}</p>
            <p>Correo: {{ $data['user_admin_email'] }} </p>
            <p>Workspace: {{ $data['workspace_name'] }}</p>

            <div style="margin-bottom: 2rem; margin-top: 2rem;">
                <p style="margin-bottom: 1rem;">Aumentar almacenamiento: <b>{{ $data['storage'] }} Gb.</b> </p>
                <p>Aumentar usuarios: <b>{{ $data['users'] }} usuarios.</b> </p>
            </div>

            <p> <span style="font-weight:bold;">Descripción:</span> {{ $data['description'] }} </p>
        </td>
    </tr>
@endsection
