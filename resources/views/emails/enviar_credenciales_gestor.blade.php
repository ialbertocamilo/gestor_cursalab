@extends('emails.plantilla_email')
@section('body')

    <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                <strong>Hola, {{$data['user']}} </strong><br> Estás recibiendo este email, porque has actualizado tu contraseña para el ingreso a tu cuenta.
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;">
        <td colspan="2" style="font-family: 'Poppins', sans-serif; padding:0; ">
            <div style="margin: 10px 95px;background-color:#FFFFFF;padding: 20px 0px;border-radius: 1rem;">

                <div style="width:25px; height:5px; margin-left:-4rem; border-radius:4px; background: #FFCD0C;"></div>
                <p style="color:#120C29; text-align:center; font-size: 1rem; margin-bottom:0px;margin-top:3px;">
                    <span style="font-weight:bold;">Usuario: </span> {{$data['email']}}
                </p>
                <p style="color:#120C29; text-align:center; font-size: 1rem; margin-bottom:0px;margin-top:3px;">
                    <span style="font-weight:bold;">Contraseña: </span> {{$data['password']}}
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
                ¿No reconoces esta actividad? Contáctate inmediatamente con el equipo de Customer Experience.<br>
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
                Este es un mensaje automático, por favor no responder.
            </p>
        </td>
    </tr>
@endsection
