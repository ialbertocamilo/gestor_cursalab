@extends('emails.pasantia_plantilla_email')
@section('body')

    <tr style="background-color:#F9FAFB; text-align:center; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <img
                src="{{ url('/img/induccion/pasantia_email.svg') }}"
                alt="Cursalab" width="150"
                style="max-width: 150;height:auto;border:none;">
        </td>
    </tr>

    <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                Hola <strong>{{ $data['lider_name'] }}</strong>, soy <strong>{{ $data['user_name'] }}</strong> y quisiera solicitar una reunión de pasantía para conocer más sobre tu área, tengo estas fechas tentativas
            </p>
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
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
            Responde a <a href="mailto:{{ $data['user_email'] }}"><span style="font-weight:bold;">{{ $data['user_email'] }}</span></a> para confirmar alguna fecha tentativa o coordinar una nueva fecha.
            </p>
        </td>
    </tr>
@endsection
