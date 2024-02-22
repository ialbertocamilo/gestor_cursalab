@extends('emails.plantilla_email')
@section('body')

    <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                <strong>Hola, {{$data['user']}} </strong><br> Estás recibiendo este email, porque hemos recibido una solicitud de recuperación
                de contraseña para tu cuenta.
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;">
        <td colspan="2" style="font-family: 'Poppins', sans-serif; padding:0; ">
            <div style="margin: 10px 95px;background-color:#FFFFFF;padding: 20px 0px;border-radius: 1rem;">

                <div style="width:25px; height:5px; margin-left:-4rem; border-radius:4px; background: #FFCD0C;"></div>
                <p
                    style="color:#120C29; text-align:center; font-size: 1.5rem; margin-bottom:18px;margin-top:3px; font-weight:bold;">
                    <a target="_blank" href="{{ $data['url_to_reset'] }}">
                        Recupera tu contraseña aquí
                    </a>
                </p>
                <div
                    style="margin-left:auto;margin-right: -3rem;width: 9px; height: 9px; border-radius: 9px; background: #CC96FC;">
                </div>

            </div>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                El enlace para recuperar contraseña expirará en  {{ $data['minutes'] }} minutos.
                Si no solicitaste la recuperación de contraseña, no es necesario realizar ninguna acción. <br>
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
                ¿No reconoces esta actividad? Comunicate inmediatamente con tu ejecutivo(a) de Customer Success.<br>
                Este es un mensaje automático, por favor no responder.
            </p>
        </td>
    </tr>
@endsection
