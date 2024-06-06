@extends('emails.pasantia_plantilla_email')
@section('body')

    <tr style="background-color:#F9FAFB; text-align:center; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <img
                src="{{ url('/img/induccion/pasantia_email.png') }}"
                alt="Cursalab" width="300"
                style="max-width: 300px;height:auto;border:none;">
        </td>
    </tr>

    <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <div style="margin-left: 95%;
                        width: 9px;
                        height: 9px;
                        border-radius: 9px;
                        background: #CC96FC;"></div>
            <div style="margin-left: 9%;
                width:25px;
                height:5px;
                border-radius:4px;
                background: #FFCD0C;"></div>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                Hola <strong>{{ $data['lider_name'] }}</strong>
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                <strong>{{ $data['user_name'] }}</strong> ha enviado una solicitud de reunión contigo, tenemos las siguientes alternativas:
            </p>
            <div style="margin-left: 90%;
                width:25px;
                height:5px;
                border-radius:4px;
                background: #FFCD0C;"></div>
            <div style="margin-left: 8%;
                        width: 9px;
                        height: 9px;
                        border-radius: 9px;
                        background: #CC96FC;"></div>
        </td>
    </tr>

    @if ($data['meeting_date_1'] && $data['meeting_time_1'])
        <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
            <td style="font-family: 'Poppins', sans-serif; ">
                <p style="color:#333D5D;font-size: .7rem;padding: 0px 46px;margin-bottom:0;">
                    Opción 1
                </p>
                <p style="color:#333D5D;font-size: 1.2rem;padding: 0px 46px;margin-top:0;">
                    <strong>{{ $data['meeting_date_1'] }} {{ $data['meeting_time_1'] }}</strong>
                </p>
            </td>
        </tr>
    @endif

    @if ($data['meeting_date_2'] && $data['meeting_time_2'])
        <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
            <td style="font-family: 'Poppins', sans-serif; ">
                <p style="color:#333D5D;font-size: .7rem;padding: 0px 46px;margin-bottom:0;">
                    Opción 2
                </p>
                <p style="color:#333D5D;font-size: 1.2rem;padding: 0px 46px;margin-top:0;">
                    <strong>{{ $data['meeting_date_2'] }} {{ $data['meeting_time_2'] }}</strong>
                </p>
            </td>
        </tr>
    @endif

    <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <div style="margin-left: 95%;
                width:25px;
                height:5px;
                border-radius:4px;
                background: #FFCD0C;"></div>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">En caso no puedas en algunas de estas opciones, envía un correo a: <a href="mailto:{{ $data['user_email'] }}" style="color: #5457E7;"><span style="font-weight:bold;">{{ $data['user_email'] }}</span></a>
            </p>
            <div style="margin-left: 8%;
                        width: 9px;
                        height: 9px;
                        border-radius: 9px;
                        background: #CC96FC;"></div>
        </td>
    </tr>
@endsection
