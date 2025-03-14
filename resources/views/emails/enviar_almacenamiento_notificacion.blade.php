@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB; text-align:start; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <div style="margin: 0 1rem;">
                <h3 style="color:#5458ea;font-size:1.4rem;margin-top: 0.5rem;">
                    Solicitud de aumento de capacidades.
                </h3>
                <p style="font-weight:bold;margin: 0.3rem 0;">Administrador: {{ $data['user_admin'] }}</p>
                <p style="margin: 0.3rem 0;">
                    Correo: {{ $data['user_admin_email'] }}</a> 
                </p>
                <p style="margin: 0.3rem 0;">
                    Workspace: {{ $data['workspace_name'] }}
                </p>
                <div style="margin-bottom: 1rem;margin-top: 1rem;">
                    <p style="margin: 0.3rem 0;display: flex;align-items: center;"> 
                        <img src="{{ url('/img/storage/cloud-outline.png') }}" style="margin-right: 0.3rem;"> Aumentar almacenamiento: 
                        <b>{{ $data['storage'] }}.</b> 
                    </p>
                    <p style="margin: 0.3rem 0;display: flex; align-items: center;">
                        <img src="{{ url('/img/storage/users-outline.png') }}" style="margin-right: .3rem;"> Aumentar usuarios: 
                        <b>{{ $data['users'] }} usuarios.</b> 
                    </p>
                    @if($data['functionalities_name'])
                        <p style="margin: 0.3rem 0;display: flex; align-items: center;">
                            Solicita la(s) siguiente(s) funcionalidad(es): 
                            <b>{{ $data['functionalities_name'] }}.</b> 
                        </p>
                    @endif
                </div>
                <p> <span style="font-weight:bold">Descripción: </span> {{ $data['description'] }} </p>
            </div>
        </td>
    </tr>
@endsection