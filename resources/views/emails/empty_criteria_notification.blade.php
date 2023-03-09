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

                This is the title

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
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
            Existen {{ $data['usersCount'] }} usuarios con criterios vacios, puedes
            descargar el <a href="">reporte aquí.</a>
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
            Este es un mensaje automático, por favor no responder.
            </p>
        </td>
    </tr>

@endsection
