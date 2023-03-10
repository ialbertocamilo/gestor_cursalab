@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td style="font-family: 'Poppins', sans-serif;">
            <h1 style="text-align:center; color:#2A3649; margin-top:2rem;font-weight:bold;font-size: 25px; padding: 0px 46px;">
                <div style="margin-left: 95%;
                          width:25px;
                          height:5px;
                          border-radius:4px;
                          background: #FFCD0C;"></div>

                Reporte de criterios

                <div style="margin-left: 8%;
                          width: 9px;
                          height: 9px;
                          border-radius: 9px;
                          background: #CC96FC;"></div>
            </h1>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB; text-align:center; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="text-align:center; margin-bottom:3px;margin-top:3px; font-weight:bold;">
                <span style="color: #CD0033; font-size: 32px">{{ $data['usersCount'] }}</span>
                <br>
                <span style="color: #CD0033; font-size: 16px">Usuarios detectados</span>
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
                Se han reportado <strong>{{ $data['usersCount'] }} usuarios</strong>
                con al menos un criterio vacío.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
                Descarga ahora el reporte.<br>
                Usa los filtros para una búsqueda más efectiva.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
                <a
                    href="{{ $data['reports-url'] }}"
                    style="background: #5457E7; text-decoration: none; color: white; border-radius: 15px; border: none; padding: 5px 40px 5px 40px">
                    Ir a reportes
                </a>
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
                Este es un mensaje automático, por favor no responder.
            </p>
        </td>
    </tr>

@endsection
